<?php
/*
 * index.php
 * Main entry point.
 * Redirects to dashboard if logged in, or login page if not.
 */

require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}