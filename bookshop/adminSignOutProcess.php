<?php

/**
 * Admin sign-out.
 *
 * Ends ONLY the admin session - a customer signed in on the same browser
 * keeps their customer session. Always redirects to the admin sign-in page.
 */

session_start();

if (isset($_SESSION["au"])) {
    unset($_SESSION["au"]);
    session_regenerate_id(true);
}

header("Location: adminSignin.php");
exit();
