<?php

    // Ensure session is started before trying to destroy it
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Unset all session variables
    session_unset();

    // Destroy the session completely
    session_destroy();

    // Redirect to the login page using an absolute path
    header("Location: /users/login.php");
    exit;

?>