<?php

/**
 * Customer sign-out.
 *
 * Ends ONLY the customer session - an admin signed in on the same browser
 * keeps their admin session.
 */

session_start();

if (isset($_SESSION["i"])) {
    unset($_SESSION["i"]);
    session_regenerate_id(true);
    echo("success");
}
