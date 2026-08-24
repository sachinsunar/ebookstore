<?php

/**
 * Builds and auto-submits the signed payment form to eSewa.
 *
 * - Validates the order belongs to the logged-in user.
 * - Only PENDING/FAILED orders can be sent for payment.
 * - Amount components are recomputed from the database, never from the browser.
 * - The secret key never leaves the server.
 */

session_start();
include_once "../Database.php";
require "esewa_lib.php";

$uuid = isset($_GET["t"]) ? preg_replace("/[^A-Za-z0-9\-]/", "", $_GET["t"]) : "";

if (!isset($_SESSION["i"])) {
    http_response_code(403);
    exit("Access denied. Please log in.");
}
if ($uuid === "") {
    http_response_code(400);
    exit("Invalid payment reference.");
}

// Order must exist, belong to this user, and still be unpaid/pending.
$rs = Connection::select(
    "SELECT * FROM `invoice`
     WHERE `transaction_uuid`='" . $uuid . "'
       AND `user_email`='" . $_SESSION["i"]["email"] . "' LIMIT 1"
);
if (!$rs || $rs->num_rows !== 1) {
    http_response_code(404);
    exit("Order not found or you are not authorized to pay it.");
}

$order = $rs->fetch_assoc();
if (!in_array($order["payment_status"], array("PENDING", "FAILED"), true)) {
    header("Location: ../invoice.php?id=" . urlencode($order["order_id"]));
    exit;
}

$cfg = esewa_config();

// Recompute amount components exactly like initiatePaymentProcess.php.
$productRs = Connection::select(
    "SELECT `price` FROM `product` WHERE `id`='" . (int)$order["product_id"] . "'"
);
if (!$productRs || $productRs->num_rows !== 1) {
    http_response_code(500);
    exit("Product for this order no longer exists.");
}
$productPrice = (float)$productRs->fetch_assoc()["price"];

$addressRs = Connection::select(
    "SELECT `district_district_id` AS did FROM `user_has_address`
     INNER JOIN `city` ON user_has_address.city_city_id=city.city_id
     WHERE `user_email`='" . $_SESSION["i"]["email"] . "'"
);

$goodsAmount = number_format($productPrice * (int)$order["qty"], 2, ".", "");
$taxAmount      = number_format((float)$cfg["tax_amount"], 2, ".", "");
$serviceCharge  = number_format((float)$cfg["service_charge"], 2, ".", "");

if ($order["payment_status"] === "FAILED") {
    // On retry, trust the stored total but keep components consistent.
    $totalAmount   = number_format((float)$order["total"], 2, ".", "");
    $deliveryCharge = number_format(
        max(0.0, (float)$totalAmount - (float)$goodsAmount - (float)$taxAmount - (float)$serviceCharge),
        2, ".", ""
    );
} else {
    if ($addressRs && $addressRs->num_rows >= 1) {
        $did = (int)$addressRs->fetch_assoc()["did"];
        $feeRs = Connection::select(
            "SELECT `delivery_fee_colombo`, `delivery_fee_other` FROM `product`
             WHERE `id`='" . (int)$order["product_id"] . "'"
        );
        $fees = $feeRs->fetch_assoc();
        $deliveryCharge = number_format(
            (float)(($did === 1) ? $fees["delivery_fee_colombo"] : $fees["delivery_fee_other"]),
            2, ".", ""
        );
    } else {
        $deliveryCharge = "0.00";
    }
    $totalAmount = number_format(
        (float)$goodsAmount + (float)$taxAmount + (float)$serviceCharge + (float)$deliveryCharge,
        2, ".", ""
    );
}

$transactionUuid = $order["transaction_uuid"];
$productCode     = $cfg["merchant_code"];
$signature       = esewa_sign($totalAmount, $transactionUuid, $productCode);

esewa_log("redirect_to_esewa", array(
    "invoice_id" => (int)$order["invoice_id"],
    "order_id"   => $order["order_id"],
    "user"       => $order["user_email"],
    "transaction_uuid" => $transactionUuid,
    "total"      => $totalAmount,
));

$fields = array(
    "amount"                  => $goodsAmount,
    "tax_amount"              => $taxAmount,
    "total_amount"            => $totalAmount,
    "transaction_uuid"        => $transactionUuid,
    "product_code"            => $productCode,
    "product_service_charge"  => $serviceCharge,
    "product_delivery_charge" => $deliveryCharge,
    "success_url"             => $cfg["success_url"],
    "failure_url"             => $cfg["failure_url"],
    "signed_field_names"      => $cfg["signed_field_names"],
    "signature"               => $signature,
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Redirecting to eSewa...</title>
</head>
<body style="font-family: Arial, sans-serif; text-align:center; padding-top:80px;">
    <h2>Redirecting to eSewa...</h2>
    <p>Please do not refresh or press the back button.</p>

    <form id="esewa-form" action="<?php echo htmlspecialchars($cfg["form_url"], ENT_QUOTES); ?>" method="POST">
        <?php foreach ($fields as $name => $value) { ?>
            <input type="hidden" name="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>"
                   value="<?php echo htmlspecialchars((string)$value, ENT_QUOTES); ?>">
        <?php } ?>
        <noscript>
            <button type="submit">Continue to eSewa</button>
        </noscript>
    </form>

    <script>
        document.getElementById("esewa-form").submit();
    </script>
</body>
</html>
