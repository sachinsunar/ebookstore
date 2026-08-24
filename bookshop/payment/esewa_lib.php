<?php

/**
 * eSewa ePay v2 helper library.
 *
 * Implements the official integration per https://developer.esewa.com.np:
 *   - HMAC-SHA256 (base64) request signing over
 *     "total_amount=...,transaction_uuid=...,product_code=..."
 *   - Response signature verification (timing-safe)
 *   - Server-to-server Transaction Status API verification
 *   - Payment logging (never logs secrets)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . "esewa_config.php";

/**
 * Generate a unique, unpredictable transaction UUID for every payment
 * attempt. Alphanumeric + hyphen only (eSewa requirement).
 */
function esewa_generate_transaction_uuid(): string
{
    return bin2hex(random_bytes(16)); // 32 hex chars
}

/**
 * Build the HMAC-SHA256 base64 signature for the standard signed fields.
 */
function esewa_sign(string $totalAmount, string $transactionUuid, string $productCode, ?string $secretKey = null): string
{
    if ($secretKey === null) {
        $cfg = esewa_config();
        $secretKey = $cfg["secret_key"];
    }
    $message = "total_amount={$totalAmount},transaction_uuid={$transactionUuid},product_code={$productCode}";
    return base64_encode(hash_hmac("sha256", $message, $secretKey, true));
}

/**
 * Verify a signed field set coming back from eSewa using a timing-safe
 * comparison. $fields must contain the raw response values.
 */
function esewa_verify_signature(array $fields, ?string $secretKey = null): bool
{
    if ($secretKey === null) {
        $cfg = esewa_config();
        $secretKey = $cfg["secret_key"];
    }

    if (empty($fields["signature"]) || empty($fields["signed_field_names"])) {
        return false;
    }

    $names = explode(",", $fields["signed_field_names"]);
    $parts = array();
    foreach ($names as $name) {
        $name = trim($name);
        if (!array_key_exists($name, $fields)) {
            return false;
        }
        $parts[] = $name . "=" . $fields[$name];
    }
    $message = implode(",", $parts);
    $expected = base64_encode(hash_hmac("sha256", $message, $secretKey, true));

    return hash_equals($expected, (string)$fields["signature"]);
}

/**
 * Decode the base64 `data` payload eSewa appends to success/failure URLs.
 */
function esewa_decode_response(string $encodedData): ?array
{
    $decoded = base64_decode($encodedData, true);
    if ($decoded === false) {
        return null;
    }
    $payload = json_decode($decoded, true);
    return is_array($payload) ? $payload : null;
}

/**
 * Server-to-server verification against eSewa's Transaction Status API.
 *
 * Returns an associative array:
 *   ["ok" => bool, "status" => string|null, "ref_id" => string|null,
 *    "total_amount" => string|null, "error" => string|null]
 *
 * "ok" is true only when eSewa was reachable AND returned a parseable
 * status. Never treat a network failure as a payment failure here - the
 * caller decides how to handle uncertain states.
 */
function esewa_check_transaction_status(string $totalAmount, string $transactionUuid, string $productCode): array
{
    $cfg = esewa_config();

    $query = http_build_query(array(
        "product_code"     => $productCode,
        "total_amount"     => $totalAmount,
        "transaction_uuid" => $transactionUuid,
    ));

    $url = $cfg["status_url"] . "?" . $query;

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
    ));
    $body   = curl_exec($ch);
    $errNo  = curl_errno($ch);
    $errMsg = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $errNo !== 0) {
        return array("ok" => false, "status" => null, "ref_id" => null, "total_amount" => null,
                     "error" => "status-api-unreachable: " . $errMsg);
    }

    $data = json_decode($body, true);

    // Some eSewa deployments return a single object, others a list of objects.
    if (isset($data[0]) && is_array($data[0])) {
        $data = $data[0];
    }

    if (!is_array($data) || !isset($data["status"])) {
        return array("ok" => false, "status" => null, "ref_id" => null, "total_amount" => null,
                     "error" => "unexpected-status-response (HTTP {$httpCode})");
    }

    return array(
        "ok"           => true,
        "status"       => strtoupper((string)$data["status"]),
        "ref_id"       => isset($data["ref_id"]) ? (string)$data["ref_id"] : null,
        "total_amount" => isset($data["total_amount"]) ? (string)$data["total_amount"] : null,
        "error"        => null,
    );
}

/**
 * Append a structured payment log line. NEVER pass secrets here.
 */
function esewa_log(string $event, array $context = array()): void
{
    $logDir = __DIR__ . DIRECTORY_SEPARATOR . "logs";
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }

    $record = array_merge(array(
        "timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
        "event"     => $event,
    ), $context);

    @file_put_contents(
        $logDir . DIRECTORY_SEPARATOR . "payments.log",
        json_encode($record) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}
