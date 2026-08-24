<?php

/**
 * eSewa FAILURE handler.
 *
 * Called when the customer cancels or payment definitively fails at eSewa.
 * The order is kept unpaid (marked FAILED) so the customer can safely retry
 * without creating a duplicate order.
 */

session_start();
include_once "../Database.php";
require "esewa_lib.php";

function renderFail(string $title, string $text, string $link, string $linkText): void
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
                    <p class="text-danger fs-5"><?php echo htmlspecialchars($text); ?></p>
                    <a href="<?php echo htmlspecialchars($link); ?>" class="btn btn-primary mt-3 px-4">
                        <?php echo htmlspecialchars($linkText); ?>
                    </a>
                    <br />
                    <a href="../purchasingHistory.php" class="btn btn-outline-secondary mt-2 px-4">
                        View Purchase History
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

$uuid = isset($_GET["t"])
    ? preg_replace("/[^A-Za-z0-9\-]/", "", $_GET["t"])
    : "";

// eSewa may append its signed data payload on failure too.
if ($uuid === "" && isset($_GET["data"])) {
    $payload = esewa_decode_response($_GET["data"]);
    if (is_array($payload) && isset($payload["transaction_uuid"])) {
        $uuid = preg_replace("/[^A-Za-z0-9\-]/", "", (string)$payload["transaction_uuid"]);
    }
}

if (!isset($_SESSION["i"])) {
    renderFail("Login required", "Please log in to view your orders.", "../signIn.php", "Go to Login");
    exit;
}

if ($uuid !== "") {
    $rs = Connection::select(
        "SELECT `invoice_id`, `order_id` FROM `invoice`
         WHERE `transaction_uuid`='" . $uuid . "'
           AND `user_email`='" . $_SESSION["i"]["email"] . "' LIMIT 1"
    );
    if ($rs && $rs->num_rows === 1) {
        $order = $rs->fetch_assoc();
        // Mark failed ONLY if not already paid - never overwrite PAID.
        Connection::iud(
            "UPDATE `invoice` SET `payment_status`='FAILED'
             WHERE `invoice_id`='" . (int)$order["invoice_id"] . "'
               AND `payment_status` IN ('PENDING','FAILED')"
        );
        esewa_log("payment_failed_by_customer", array(
            "invoice_id" => (int)$order["invoice_id"],
            "order_id"   => $order["order_id"],
            "transaction_uuid" => $uuid,
        ));
        renderFail("Payment failed",
            "Payment failed. Your order has not been marked as paid. Please try again.",
            "retryPaymentProcess.php?order_id=" . urlencode($order["order_id"]),
            "Retry Payment");
        exit;
    }
}

esewa_log("payment_failed_no_order", array("user" => $_SESSION["i"]["email"]));

renderFail("Payment failed",
    "Payment failed. Your order has not been marked as paid. Please try again.",
    "../cart.php", "Back to Cart");
