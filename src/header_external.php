<?php
/*
 * header_external.php
 * Special header for the external API flow.
 * It logs out any active user and shows a unique header.
 */

require_once 'config.php';

// --- AUTOMATIC LOGOUT ---
if (isset($_SESSION['user_id'])) {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Delivery - Sustainable Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header data-external="true">
    <div class="header-container">
        <a href="#" class="logo">Sustainable Delivery</a>
        <nav>
            <span>External Marketplace</span>
        </nav>
    </div>
</header>

<main>