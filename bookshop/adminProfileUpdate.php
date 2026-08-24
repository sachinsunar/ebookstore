<?php
include "Database.php";
session_start();

if (!isset($_SESSION["au"])) {
    echo ("Please sign in as admin.");
    exit();
}

if(isset($_POST["e"]) && isset($_POST["f"]) && isset($_POST["l"])){

    $email = $_POST["e"];
    $fname = $_POST["f"];
    $lname = $_POST["l"];

            Connection::iud("UPDATE `admin` SET `fname`='".$fname."',`lname`='".$lname."' WHERE `email`='".$email."'");
            $_SESSION["au"]["fname"] = $fname;
            $_SESSION["au"]["lname"] = $lname;
            echo ("success");

}else{
    echo ("Somthing is missing.");
}

?>