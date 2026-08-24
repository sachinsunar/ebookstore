<?php

/**
 * eSewa configuration loader.
 *
 * Credentials are kept OUTSIDE application source code. Values are read in
 * this priority order:
 *   1. Environment variables  (ESEWA_*)
 *   2. payment/config.ini     (real credentials, git-ignored)
 *   3. Official public eSewa SANDBOX defaults (local development only)
 */

function esewa_env(string $key)
{
    $value = getenv($key);
    if ($value !== false && $value !== "") {
        return $value;
    }

    static $ini = null;
    if ($ini === null) {
        $ini = array();
        $iniFile = __DIR__ . DIRECTORY_SEPARATOR . "config.ini";
        if (is_readable($iniFile)) {
            $parsed = parse_ini_file($iniFile, false, INI_SCANNER_RAW);
            if (is_array($parsed)) {
                $ini = $parsed;
            }
        }
    }

    if (isset($ini[$key]) && trim((string)$ini[$key]) !== "") {
        return trim((string)$ini[$key]);
    }

    return null;
}

function esewa_config(): array
{
    $environment = strtolower(esewa_env("ESEWA_ENVIRONMENT") ?: "sandbox");

    // Official endpoints per https://developer.esewa.com.np (ePay v2).
    if ($environment === "production") {
        $formUrl   = "https://epay.esewa.com.np/api/epay/main/v2/form";
        $statusUrl = "https://epay.esewa.com.np/api/epay/transaction/status/";
    } else {
        $environment = "sandbox";
        $formUrl   = "https://rc-epay.esewa.com.np/api/epay/main/v2/form";
        $statusUrl = "https://rc-epay.esewa.com.np/api/epay/transaction/status/";
    }

    return array(
        "environment"      => $environment,
        "merchant_code"    => esewa_env("ESEWA_MERCHANT_CODE") ?: "EPAYTEST",
        "secret_key"       => esewa_env("ESEWA_SECRET_KEY") ?: "8gBm/:&EnhH.1/q",

        // Advanced/testing override - lets you point the integration at a
        // local mock of the eSewa endpoints. Leave unset for real usage;
        // production/sandbox hosts are chosen automatically otherwise.
        "form_url"         => esewa_env("ESEWA_FORM_URL") ?: $formUrl,
        "status_url"       => esewa_env("ESEWA_STATUS_URL") ?: $statusUrl,

        "app_base_url"     => rtrim(esewa_env("ESEWA_APP_BASE_URL") ?: "http://localhost/ebookstore/bookshop", "/"),
        "success_url"      => rtrim(esewa_env("ESEWA_APP_BASE_URL") ?: "http://localhost/ebookstore/bookshop", "/") . "/payment/payment_success.php",
        "failure_url"      => rtrim(esewa_env("ESEWA_APP_BASE_URL") ?: "http://localhost/ebookstore/bookshop", "/") . "/payment/payment_failure.php",
        "tax_amount"       => esewa_env("ESEWA_TAX_AMOUNT") ?: "0",
        "service_charge"   => esewa_env("ESEWA_SERVICE_CHARGE") ?: "0",
        "signed_field_names" => "total_amount,transaction_uuid,product_code",
    );
}
