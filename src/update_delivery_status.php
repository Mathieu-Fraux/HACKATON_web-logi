<?php
/*
 * update_delivery_status.php
 * Handles POST request from dashboard to update a delivery's status.
 */

require_once 'config.php'; // Includes PDO and starts session

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, send to login
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $id_delivery = $_POST['id_delivery'] ?? null;
    $new_status = $_POST['status'] ?? null;

    // Basic validation
    if (!empty($id_delivery) && !empty($new_status)) {
        try {
            // **Crucial Security Check:**
            // Verify this delivery belongs to this user before updating
            $stmt_check = $pdo->prepare("SELECT 1 FROM DELIVERY WHERE id_delivery = ? AND id_user_assigned = ?");
            $stmt_check->execute([$id_delivery, $user_id]);
            
            if ($stmt_check->fetch()) {
                // User is authorized, proceed with update
                $stmt_update = $pdo->prepare("UPDATE DELIVERY SET status = ? WHERE id_delivery = ?");
                $stmt_update->execute([$new_status, $id_delivery]);
                
                // Redirect back to dashboard (ideally with a success message)
                // For simplicity, we just redirect.
                header('Location: dashboard.php');
                exit;
            } else {
                // Error: Delivery not found or doesn't belong to user
                // You could set a session flash message here
                header('Location: dashboard.php'); // Redirect anyway
                exit;
            }

        } catch (PDOException $e) {
            // Handle database error
            // For a real app, log this error
            die('Database error: ' . $e->getMessage());
        }
    }
}

// If accessed directly or with bad data, just go back to dashboard
header('Location: dashboard.php');
exit;
