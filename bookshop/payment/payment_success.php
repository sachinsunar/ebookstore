<?php

/**
 * eSewa SUCCESS handler - the ONLY place an order becomes PAID.
 *
 * Verification chain (all steps must pass):
 *   1. Authenticated customer with a valid signed eSewa payload.
 *   2. Response signature verified (HMAC-SHA256, timing-safe).
 *   3. Order located by transaction UUID and ownership confirmed.
 *   4. Product code matches our configured merchant code.
 *   5. Amount compared against the SERVER-side stored order total.
 *   6. Transaction verified server-to-server via eSewa Status API = COMPLETE.
 *   7. Atomic PENDING/FAILED -> PAID transition (replay/duplicate safe).
 *
 * Uncertain states NEVER mark the order failed - they keep it PENDING and
 * ask the customer to contact support.
 */

session_start();
include_once "../Database.php";
require "esewa_lib.php";

$encodedData = isset($_GET["data"]) ? $_GET["data"] : "";

function renderMessage(string $title, string $text, string $style, ?string $link = null, string $linkText = ""): void
{
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        <link rel="stylesheet" href="../bootstrap.css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    </head>
    <body style="background-color:#c5e1fa;background-image:linear-gradient(90deg,#c5e1fa 0%,#376894 100%);">
        <div class="container" style="max-width:560px;margin-top:90px;">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-body text-center p-5">
                    <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($title); ?></h3>
                    <p class="<?php echo $style; ?> fs-5"><?php echo htmlspecialchars($text); ?></p>
                    <?php if ($link !== null) { ?>
                        <a href="<?php echo htmlspecialchars($link); ?>" class="btn btn-primary mt-3 px-4">
                            <?php echo htmlspecialchars($linkText ?: "Continue"); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

if (!isset($_SESSION["i"])) {
    esewa_log("success_no_session", array("ip" => $_SERVER["REMOTE_ADDR"] ?? ""));
    renderMessage("Login required", "Please log in to complete your payment verification.",
        "text-danger", "../signIn.php", "Go to Login");
    exit;
}

if ($encodedData === "") {
    esewa_log("success_missing_data", array("user" => $_SESSION["i"]["email"]));
    header("Location: ../cart.php");
    exit;
}

$cfg     = esewa_config();
$payload = esewa_decode_response($encodedData);

// --- Step 1+2: payload present and signature valid -------------------------
if ($payload === null || !isset($payload["transaction_uuid"])) {
    esewa_log("verification_failed", array(
        "stage" => "payload_decode", "user" => $_SESSION["i"]["email"],
    ));
    renderMessage("We could not verify your payment",
        "We could not verify your payment. Please contact support if money was deducted.",
        "text-warning", "../purchasingHistory.php", "View Purchase History");
    exit;
}

$uuid = preg_replace("/[^A-Za-z0-9\-]/", "", (string)$payload["transaction_uuid"]);

if (!esewa_verify_signature($payload)) {
    // Tampered or forged callback - do NOT trust anything in it.
    esewa_log("verification_failed", array(
        "stage" => "response_signature", "transaction_uuid" => $uuid,
        "user"  => $_SESSION["i"]["email"], "ip" => $_SERVER["REMOTE_ADDR"] ?? "",
    ));
    renderMessage("We could not verify your payment",
        "We could not verify your payment. Please contact support if money was deducted.",
        "text-warning", "../purchasingHistory.php", "View Purchase History");
    exit;
}

// --- Step 3: locate the order and confirm ownership ------------------------
$rs = Connection::select(
    "SELECT * FROM `invoice` WHERE `transaction_uuid`='" . $uuid . "' LIMIT 1"
);
if (!$rs || $rs->num_rows !== 1) {
    esewa_log("verification_failed", array(
        "stage" => "order_not_found", "transaction_uuid" => $uuid,
        "user"  => $_SESSION["i"]["email"],
    ));
    renderMessage("Order not found",
        "We could not match this payment to an order. Please contact support if money was deducted.",
        "text-warning", "../purchasingHistory.php", "View Purchase History");
    exit;
}
$order = $rs->fetch_assoc();

if ($order["user_email"] !== $_SESSION["i"]["email"]) {
    esewa_log("verification_failed", array(
        "stage" => "ownership_mismatch", "transaction_uuid" => $uuid,
        "session_user" => $_SESSION["i"]["email"], "order_owner" => $order["user_email"],
        "ip" => $_SERVER["REMOTE_ADDR"] ?? "",
    ));
    http_response_code(403);
    renderMessage("Access denied", "You are not authorized to view this order.",
        "text-danger", "../home.php", "Back to Home");
    exit;
}

$orderId = $order["order_id"];

// --- Step 4: replay guard --------------------------------------------------
if ($order["payment_status"] === "PAID") {
    // Already processed - never process twice, just show the receipt.
    esewa_log("duplicate_callback_ignored", array(
        "invoice_id" => (int)$order["invoice_id"], "transaction_uuid" => $uuid,
    ));
    header("Location: ../invoice.php?id=" . urlencode($orderId));
    exit;
}

// --- Step 5: product code must be ours -------------------------------------
if (!isset($payload["product_code"]) || $payload["product_code"] !== $cfg["merchant_code"]) {
    esewa_log("verification_failed", array(
        "stage" => "product_code_mismatch", "transaction_uuid" => $uuid,
        "expected" => $cfg["merchant_code"], "received" => $payload["product_code"] ?? null,
    ));
    renderMessage("We could not verify your payment",
        "We could not verify your payment. Please contact support if money was deducted.",
        "text-warning", "../purchasingHistory.php", "View Purchase History");
    exit;
}

// --- Step 6: amount must equal the server-stored order total ---------------
$dbAmount    = number_format((float)$order["total"], 2, ".", "");
$paidAmount  = number_format((float)($payload["total_amount"] ?? 0), 2, ".", "");
if (bccomp($dbAmount, $paidAmount, 2) !== 0) {
    esewa_log("verification_failed", array(
        "stage" => "amount_mismatch", "transaction_uuid" => $uuid,
        "expected" => $dbAmount, "received" => $paidAmount,
    ));
    renderMessage("We could not verify your payment",
        "We could not verify your payment. Please contact support if money was deducted.",
        "text-warning", "../purchasingHistory.php", "View Purchase History");
    exit;
}

// --- Step 7: authoritative server-to-server status check -------------------
$statusResult = esewa_check_transaction_status($dbAmount, $uuid, $cfg["merchant_code"]);

if (!$statusResult["ok"]) {
    // Network/API problem - uncertain state. Never guess.
    esewa_log("verification_unavailable", array(
        "transaction_uuid" => $uuid, "error" => $statusResult["error"],
    ));
    renderMessage("We could not verify your payment",
        "Your payment is being processed but we could not confirm it right now. Please try again shortly or contact support if money was deducted.",
        "text-warning", "../purchasingHistory.php", "View Purchase History");
    exit;
}

if ($statusResult["status"] === "COMPLETE") {

    // Cross-check the API response fields too.
    $apiAmount = number_format((float)$statusResult["total_amount"], 2, ".", "");
    if (bccomp($dbAmount, $apiAmount, 2) !== 0 || empty($statusResult["ref_id"])) {
        esewa_log("verification_failed", array(
            "stage" => "api_crosscheck", "transaction_uuid" => $uuid,
            "expected" => $dbAmount, "received" => $apiAmount,
        ));
        renderMessage("We could not verify your payment",
            "We could not verify your payment. Please contact support if money was deducted.",
            "text-warning", "../purchasingHistory.php", "View Purchase History");
        exit;
    }

    $invoiceId = (int)$order["invoice_id"];
    $productId = (int)$order["product_id"];
    $qty       = (int)$order["qty"];
    $refId     = $statusResult["ref_id"] ?? "";
    $now = new DateTime("now", new DateTimeZone("Asia/Kathmandu"));
    $paidAt = $now->format("Y-m-d H:i:s");

    // Atomic transition - guarantees a single processing even under races
    // or duplicate callbacks.
    Connection::iud(
        "UPDATE `invoice`
         SET `payment_status`='PAID', `transaction_code`='" . $refId . "', `paid_at`='" . $paidAt . "'
         WHERE `invoice_id`='" . $invoiceId . "' AND `payment_status` IN ('PENDING','FAILED')"
    );

    if (Connection::$connection->affected_rows === 1) {

        // Reduce stock once, guarded so it can never go negative.
        Connection::iud(
            "UPDATE `product`
             SET `qty`=`qty`-" . $qty . "
             WHERE `id`='" . $productId . "' AND `qty`>=" . $qty
        );

        // Clear this item from the customer's cart only AFTER confirmation.
        Connection::iud(
            "DELETE FROM `cart`
             WHERE `user_email`='" . $order["user_email"] . "' AND `product_id`='" . $productId . "'"
        );

        esewa_log("payment_confirmed", array(
            "invoice_id" => $invoiceId, "order_id" => $orderId,
            "transaction_uuid" => $uuid, "transaction_code" => $refId,
            "amount" => $dbAmount, "user" => $order["user_email"],
        ));

        header("Location: ../invoice.php?id=" . urlencode($orderId));
        exit;
    }

    // Affected rows 0 -> already marked PAID by another request (race).
    esewa_log("duplicate_callback_ignored", array(
        "invoice_id" => $invoiceId, "transaction_uuid" => $uuid, "stage" => "atomic_update",
    ));
    header("Location: ../invoice.php?id=" . urlencode($orderId));
    exit;

} elseif (in_array($statusResult["status"], array("CANCELED", "NOT_FOUND"), true)) {
    // Definitive non-payment per eSewa.
    Connection::iud(
        "UPDATE `invoice` SET `payment_status`='FAILED'
         WHERE `invoice_id`='" . (int)$order["invoice_id"] . "'
           AND `payment_status` IN ('PENDING','FAILED')"
    );
    esewa_log("payment_not_completed", array(
        "invoice_id" => (int)$order["invoice_id"], "transaction_uuid" => $uuid,
        "esewa_status" => $statusResult["status"],
    ));
    renderMessage("Payment failed",
        "Payment failed. Your order has not been marked as paid. Please try again.",
        "text-danger", "esewa_redirect.php?t=" . urlencode($uuid), "Retry Payment");
    exit;
}

// PENDING / AMBIGUOUS / anything else -> still processing, do not decide yet.
esewa_log("payment_pending_at_esewa", array(
    "invoice_id" => (int)$order["invoice_id"], "transaction_uuid" => $uuid,
    "esewa_status" => $statusResult["status"],
));
renderMessage("Payment is being processed",
    "Your payment is still being processed by eSewa. We will confirm it shortly. If money was deducted please contact support.",
    "text-warning", "../purchasingHistory.php", "View Purchase History");
exit;
