<?php
// header.php
// Includes config, starts session, and outputs HTML <head> and nav

// Ensure config is included.
// This will also start the session.
require_once 'config.php';

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_login = $is_logged_in ? $_SESSION['user_login'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sustainable Delivery</title>
    <!-- Link to the single external stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="main-nav">
    <a href="index.php" class="logo">Sustainable Delivery</a>
    <nav>
        <?php if ($is_logged_in): ?>
            <!-- Show these links if user is logged in -->
            <span>Hello, <?php echo htmlspecialchars($user_login); ?>!</span>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <!-- Show these links if user is logged out -->
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <!-- Page content will go here -->
