<?php
/*
 * update_delivery_status.php
 * Handles status updates for deliveries.
 * Only allows drivers to update their own assigned deliveries.
 * Enforces valid status transitions.
 */

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$id_delivery = $_POST['id_delivery'] ?? null;
$new_status = $_POST['status'] ?? null;

// Valid statuses
$valid_statuses = ['assigned', 'in_progress', 'completed', 'cancelled'];

if (empty($id_delivery) || empty($new_status)) {
    $_SESSION['update_error'] = 'Missing delivery ID or status.';
    header('Location: dashboard.php');
    exit;
}

if (!in_array($new_status, $valid_statuses)) {
    $_SESSION['update_error'] = 'Invalid status.';
    header('Location: dashboard.php');
    exit;
}

try {
    // Verify this delivery belongs to this user and get current status
    $stmt_check = $pdo->prepare(
        "SELECT status, id_user_assigned 
         FROM DELIVERY 
         WHERE id_delivery = ?"
    );
    $stmt_check->execute([$id_delivery]);
    $delivery = $stmt_check->fetch();
    
    if (!$delivery) {
        $_SESSION['update_error'] = 'Delivery not found.';
        header('Location: dashboard.php');
        exit;
    }

    // Verify ownership - only assigned user can update
    if ($delivery['id_user_assigned'] != $user_id) {
        $_SESSION['update_error'] = 'You can only update your own deliveries.';
        header('Location: dashboard.php');
        exit;
    }

    $current_status = $delivery['status'];

    // Prevent updates to already completed/cancelled deliveries
    if (in_array($current_status, ['completed', 'cancelled'])) {
        $_SESSION['update_error'] = 'Cannot update a completed or cancelled delivery.';
        header('Location: dashboard.php');
        exit;
    }

    // Enforce valid status transitions (optional strict enforcement)
    // Allow forward progression: assigned -> in_progress -> completed
    // Allow cancellation from any active state
    $valid_transition = false;
    
    if ($new_status === 'cancelled') {
        // Can cancel from any active state
        $valid_transition = true;
    } elseif ($current_status === 'assigned' && in_array($new_status, ['in_progress', 'completed'])) {
        $valid_transition = true;
    } elseif ($current_status === 'in_progress' && $new_status === 'completed') {
        $valid_transition = true;
    } elseif ($current_status === $new_status) {
        // Same status - allow (no-op)
        $valid_transition = true;
    }

    if (!$valid_transition) {
        $_SESSION['update_error'] = "Cannot transition from {$current_status} to {$new_status}.";
        header('Location: dashboard.php');
        exit;
    }

    // Update the delivery status
    $stmt_update = $pdo->prepare(
        "UPDATE DELIVERY 
         SET status = ? 
         WHERE id_delivery = ?"
    );
    $stmt_update->execute([$new_status, $id_delivery]);
    
    $_SESSION['update_success'] = "Delivery #{$id_delivery} status updated to {$new_status}.";
    header('Location: dashboard.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['update_error'] = 'Database error occurred while updating delivery.';
    error_log('Status update error: ' . $e->getMessage());
    header('Location: dashboard.php');
    exit;
}