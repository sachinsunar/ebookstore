<?php

include "Database.php";
require_once "mail_config.php";

include "SMTP.php";
include "PHPMailer.php";
include "Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

if (isset($_GET["e"])) {

    $email = $_GET["e"];

    $rs = Connection::select("SELECT * FROM `user` WHERE `email`='" . $email . "'");
    $n = $rs->num_rows;

    if ($n == 1) {

        $code = uniqid();
        Connection::iud("UPDATE `user` SET `verification_code`='" . $code . "' WHERE `email`='" . $email . "'");

        $mail = new PHPMailer;
        app_mail_configure($mail);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Unicorn Forgot password Verification Code';
        $bodyContent = '<h2 style="color:green;">Your Verification Code is </h2><h1 style="color:red">'.$code.'</h1>';
        $mail->Body    = $bodyContent;

        if(app_mail_deliver($mail, $code)){
            echo 'Success';
        }else{
            echo 'Verification code sending failed.';
        }

    } else {
        echo ("Invalid Email Address");
    }
} else {
    echo ("Please Enter Your Email Address in Email Field.");
}
