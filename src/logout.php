<?php
/*
 * logout.php
 * Destroys the user session and redirects to login.
 */

require_once 'config.php'; // This will start the session

// Unset all session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect to the login page
header("Location: login.php");
exit;
