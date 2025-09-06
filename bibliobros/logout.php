<?php
require_once __DIR__ . '/config.php'; // starts session

// Clear all session variables
session_unset(); // remove all data from $_SESSION

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session data on the server side
session_destroy(); // end the session :contentReference[oaicite:1]{index=1}

// Redirect to login page
header('Location: Toplogin.php');
exit;
