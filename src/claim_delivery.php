<?php
/*
 * claim_delivery.php
 * Handles driver claiming of available deliveries.
 * Atomic operation ensures only one driver can claim each delivery.
 */

require_once 'config.php';

// Must be logged in to claim
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$delivery_id = $_POST['id_delivery'] ?? null;

if (empty($delivery_id)) {
    $_SESSION['claim_error'] = 'Invalid delivery ID.';
    header('Location: dashboard.php');
    exit;
}

try {
    // Start transaction for atomic claim operation
    $pdo->beginTransaction();

    // Check if delivery is still available (with row lock)
    $stmt_check = $pdo->prepare(
        "SELECT id_delivery, status, id_user_assigned 
         FROM DELIVERY 
         WHERE id_delivery = ? 
         FOR UPDATE"
    );
    $stmt_check->execute([$delivery_id]);
    $delivery = $stmt_check->fetch();

    // Verify delivery exists
    if (!$delivery) {
        $pdo->rollBack();
        $_SESSION['claim_error'] = 'Delivery not found.';
        header('Location: dashboard.php');
        exit;
    }

    // Verify delivery is available (not already claimed)
    if ($delivery['status'] !== 'available' || $delivery['id_user_assigned'] !== null) {
        $pdo->rollBack();
        $_SESSION['claim_error'] = 'This delivery has already been claimed by another driver.';
        header('Location: dashboard.php');
        exit;
    }

    // Claim the delivery - set assignee, update status, record claim timestamp
    $stmt_claim = $pdo->prepare(
        "UPDATE DELIVERY 
         SET id_user_assigned = ?, 
             status = 'assigned', 
             claimed_at = NOW() 
         WHERE id_delivery = ?"
    );
    $stmt_claim->execute([$user_id, $delivery_id]);

    // Commit transaction
    $pdo->commit();

    // Set success message
    $_SESSION['claim_success'] = "Delivery #{$delivery_id} successfully claimed!";
    header('Location: dashboard.php');
    exit;

} catch (PDOException $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['claim_error'] = 'An error occurred while claiming the delivery. Please try again.';
    error_log('Claim error: ' . $e->getMessage());
    header('Location: dashboard.php');
    exit;
}