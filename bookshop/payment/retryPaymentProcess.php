<?php

/**
 * Payment retry.
 *
 * Takes an existing PENDING/FAILED unpaid order owned by the logged-in user,
 * attaches a NEW transaction UUID to it (same order - no duplicates) and
 * sends the customer back to eSewa.
 */

session_start();
include_once "../Database.php";
require "esewa_lib.php";

if (!isset($_SESSION["i"])) {
    header("Location: ../signIn.php");
    exit;
}

$orderId = isset($_GET["order_id"]) ? preg_replace("/[^A-Za-z0-9]/", "", $_GET["order_id"]) : "";

if ($orderId === "") {
    http_response_code(400);
    exit("Invalid order reference.");
}

// Ownership + state check: only the owner may retry an unpaid order.
$rs = Connection::select(
    "SELECT `invoice_id`, `transaction_uuid`, `payment_status`
     FROM `invoice`
     WHERE `order_id`='" . $orderId . "' AND `user_email`='" . $_SESSION["i"]["email"] . "'
     ORDER BY `invoice_id` DESC LIMIT 1"
);

if (!$rs || $rs->num_rows !== 1) {
    http_response_code(403);
    exit("Access denied.");
}

$order = $rs->fetch_assoc();

if (!in_array($order["payment_status"], array("PENDING", "FAILED"), true)) {
    // Already paid (or otherwise finalized) -> just show the invoice.
    header("Location: ../invoice.php?id=" . urlencode($orderId));
    exit;
}

$newUuid = esewa_generate_transaction_uuid();

Connection::iud(
    "UPDATE `invoice`
     SET `transaction_uuid`='" . $newUuid . "', `payment_status`='PENDING',
         `transaction_code`=NULL, `paid_at`=NULL
     WHERE `invoice_id`='" . (int)$order["invoice_id"] . "'"
);

esewa_log("payment_retry_initiated", array(
    "invoice_id" => (int)$order["invoice_id"],
    "order_id"   => $orderId,
    "user"       => $_SESSION["i"]["email"],
    "old_transaction_uuid" => $order["transaction_uuid"],
    "new_transaction_uuid" => $newUuid,
));

header("Location: esewa_redirect.php?t=" . urlencode($newUuid));
exit;
