<?php
session_start();
include "Database.php";
require_once "mail_config.php";

require "SMTP.php";
require "PHPMailer.php";
require "Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION["au"])) {
    echo ("Please sign in as admin.");
    exit();
}

if (isset($_POST["email"]) && isset($_POST["name"])) {
    if ($_SESSION["au"]["email"] == $_POST["email"]) {

        $cname = $_POST["name"];
        $umail = $_POST["email"];

        $category_rs = Connection::select("SELECT * FROM `category` WHERE `cat_name` LIKE '%" . $cname . "%'");
        $category_num = $category_rs->num_rows;

        if ($category_num == 0) {

            $code = uniqid();
            Connection::iud("UPDATE `admin` SET `vcode`='" . $code . "' WHERE `email`='" . $umail . "'");

            $mail = new PHPMailer;
            app_mail_configure($mail);
            $mail->addAddress($umail);
            $mail->isHTML(true);
            $mail->Subject = 'Unicorn Admin Login Verification Code for Add new category';
            $bodyContent = '<h1 style="color:blue;">Your Verification Code is</h1><h2 style="color:red;"> ' . $code . '</h2>';
            $mail->Body    = $bodyContent;

            if (!$mail->send()) {
                echo 'Verification code sending failed.';
            } else {
                echo 'Success';
            }
        } else {
            echo ("This category already exists.");
        }
    } else {
        echo ("Invalid user.");
    }
} else {
    echo ("Something is missing.");
}
