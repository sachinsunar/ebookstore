<?php

/**
 * Payment initiation.
 *
 * Creates a PENDING order BEFORE the customer is sent to eSewa and returns
 * the redirect target for the signed eSewa form.
 *
 * The authoritative amount is ALWAYS computed here from the database - the
 * browser never sends an amount.
 */

session_start();
include_once "../Database.php";
require "esewa_lib.php";

header("Content-Type: application/json");

if (!isset($_SESSION["i"])) {
    echo json_encode(array("status" => "error", "code" => "login",
        "message" => "Please log in to continue."));
    exit;
}

$userEmail = $_SESSION["i"]["email"];
$productId = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
$qty       = isset($_POST["qty"]) ? (int)$_POST["qty"] : 0;

if ($productId <= 0 || $qty <= 0 || $qty > 999) {
    echo json_encode(array("status" => "error", "code" => "input",
        "message" => "Invalid product or quantity."));
    exit;
}

// Product must exist and have enough stock.
$productRs = Connection::select(
    "SELECT * FROM `product` WHERE `id`='" . $productId . "'"
);
if (!$productRs || $productRs->num_rows !== 1) {
    echo json_encode(array("status" => "error", "code" => "input",
        "message" => "Product not found."));
    exit;
}
$product = $productRs->fetch_assoc();

if ((int)$product["qty"] < $qty) {
    echo json_encode(array("status" => "error", "code" => "stock",
        "message" => "Not enough stock available."));
    exit;
}

// Delivery destination from the user's saved address.
$addressRs = Connection::select(
    "SELECT `district_district_id` AS did FROM `user_has_address`
     INNER JOIN `city` ON user_has_address.city_city_id=city.city_id
     WHERE `user_email`='" . $userEmail . "'"
);
if (!$addressRs || $addressRs->num_rows < 1) {
    echo json_encode(array("status" => "error", "code" => "profile",
        "message" => "Please update your profile address first."));
    exit;
}
$did = (int)$addressRs->fetch_assoc()["did"];

// Server-side total calculation. Money is handled as exact decimal strings.
$cfg = esewa_config();
$unitPrice    = number_format((float)$product["price"], 2, ".", "");
$goodsAmount  = number_format((float)$unitPrice * $qty, 2, ".", "");
$deliveryFee  = ($did === 1)
    ? $product["delivery_fee_colombo"]
    : $product["delivery_fee_other"];
$deliveryFee  = number_format((float)$deliveryFee, 2, ".", "");
// eSewa rule: total_amount = amount + tax + service_charge + delivery_charge.
$totalAmount  = number_format(
    (float)$goodsAmount + (float)$deliveryFee
        + (float)number_format((float)$cfg["tax_amount"], 2, ".", "")
        + (float)number_format((float)$cfg["service_charge"], 2, ".", ""),
    2, ".", ""
);

$now = new DateTime("now", new DateTimeZone("Asia/Kathmandu"));
$dateStr = $now->format("Y-m-d H:i:s");

// Reuse an existing unpaid order for the same user+product instead of
// creating duplicates when retrying (PENDING or FAILED only).
$existingRs = Connection::select(
    "SELECT `invoice_id`, `order_id`, `transaction_uuid`, `payment_status`
     FROM `invoice`
     WHERE `user_email`='" . $userEmail . "' AND `product_id`='" . $productId . "'
       AND `payment_status` IN ('PENDING','FAILED')
     ORDER BY `invoice_id` DESC LIMIT 1"
);

$newUuid = esewa_generate_transaction_uuid();

if ($existingRs && $existingRs->num_rows === 1) {
    // Retry path: refresh quantity/amount/date on the SAME order row and
    // attach a fresh transaction UUID for the new attempt.
    $existing = $existingRs->fetch_assoc();
    $invoiceId = (int)$existing["invoice_id"];

    Connection::iud(
        "UPDATE `invoice`
         SET `qty`='" . $qty . "', `total`='" . $totalAmount . "', `date`='" . $dateStr . "',
             `transaction_uuid`='" . $newUuid . "', `payment_method`='esewa',
             `payment_status`='PENDING', `transaction_code`=NULL, `paid_at`=NULL,
             `order_status_status_id`='1'
         WHERE `invoice_id`='" . $invoiceId . "'"
    );

    esewa_log("payment_initiated_retry", array(
        "invoice_id" => $invoiceId,
        "order_id"   => $existing["order_id"],
        "user"       => $userEmail,
        "product_id" => $productId,
        "qty"        => $qty,
        "total"      => $totalAmount,
        "new_transaction_uuid" => $newUuid,
    ));
} else {
    // Fresh order: created as PENDING - order created != paid.
    Connection::iud(
        "INSERT INTO `invoice`
            (`order_id`,`date`,`total`,`qty`,`product_id`,`user_email`,
             `order_status_status_id`,`transaction_uuid`,`payment_method`,`payment_status`)
         VALUES
            ('" . uniqid() . "','" . $dateStr . "','" . $totalAmount . "','" . $qty . "',
             '" . $productId . "','" . $userEmail . "','1','" . $newUuid . "','esewa','PENDING')"
    );

    $invoiceId = (int)Connection::$connection->insert_id;

    esewa_log("payment_initiated", array(
        "invoice_id" => $invoiceId,
        "user"       => $userEmail,
        "product_id" => $productId,
        "qty"        => $qty,
        "total"      => $totalAmount,
        "transaction_uuid" => $newUuid,
    ));
}

echo json_encode(array(
    "status"   => "ok",
    "redirect" => "payment/esewa_redirect.php?t=" . urlencode($newUuid),
));
