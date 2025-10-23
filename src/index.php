<?php
/*
 * index.php
 * Main entry point.
 * Redirects to dashboard if logged in, or login page if not.
 */

require_once 'config.php'; // This starts the session

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to their dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // User is not logged in, redirect to the login page
    header('Location: login.php');
    exit;
}
