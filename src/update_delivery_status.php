<?php
/*
 * update_delivery_status.php
 * Handles POST request from dashboard to update a delivery's status.
 */

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $id_delivery = $_POST['id_delivery'] ?? null;
    $new_status = $_POST['status'] ?? null;

    if (!empty($id_delivery) && !empty($new_status)) {
        try {
            // Verify this delivery belongs to this user
            $stmt_check = $pdo->prepare("SELECT 1 FROM DELIVERY WHERE id_delivery = ? AND id_user_assigned = ?");
            $stmt_check->execute([$id_delivery, $user_id]);
            
            if ($stmt_check->fetch()) {
                $stmt_update = $pdo->prepare("UPDATE DELIVERY SET status = ? WHERE id_delivery = ?");
                $stmt_update->execute([$new_status, $id_delivery]);
                
                header('Location: dashboard.php');
                exit;
            } else {
                header('Location: dashboard.php');
                exit;
            }

        } catch (PDOException $e) {
            die('Database error: ' . $e->getMessage());
        }
    }
}

header('Location: dashboard.php');
exit;