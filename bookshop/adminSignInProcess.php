<?php

/**
 * Admin sign-in with email + password.
 *
 * Replaces the old email-verification-code login. On success the admin row
 * is stored in $_SESSION["au"] exactly like the previous flow, so all
 * existing admin pages keep working unchanged.
 */

session_start();
include "Database.php";

if (isset($_POST["e"]) && isset($_POST["p"])) {

    $email    = trim($_POST["e"]);
    $password = $_POST["p"];

    if (empty($email)) {
        echo ("Please enter your email.");
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo ("Invalid email address.");
    } else if (empty($password)) {
        echo ("Please enter your password.");
    } else {

        $admin_rs = Connection::select(
            "SELECT * FROM `admin` WHERE `email`='" . $email . "' LIMIT 1"
        );

        if (!$admin_rs || $admin_rs->num_rows !== 1) {
            echo ("Invalid email or password.");
        } else {
            $admin_data = $admin_rs->fetch_assoc();

            if ($admin_data["password"] === "" || !hash_equals($admin_data["password"], $password)) {
                echo ("Invalid email or password.");
            } else {
                session_regenerate_id(true);
                $_SESSION["au"] = $admin_data;
                echo ("success");
            }
        }
    }
} else {
    echo ("Something went wrong. Please try again.");
}
