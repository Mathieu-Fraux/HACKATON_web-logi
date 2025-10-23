<?php
// header.php
// Includes config, starts session, and outputs HTML <head> and nav

require_once 'config.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_login = $is_logged_in ? $_SESSION['user_login'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sustainable Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="header-container">
        <a href="index.php" class="logo">Sustainable Delivery</a>
        <nav>
            <?php if ($is_logged_in): ?>
                <span class="user-greeting"><?php echo htmlspecialchars($user_login); ?></span>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>