<?php
/*
 * assign_delivery.php
 * Handles the form submission from api_create_delivery.php (deliverer selection).
 * Creates the final delivery record with an assigned user.
 */

require_once 'config.php';

// Automatic logout for external API
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
    session_start();
}

$error_message = '';
$success_message = '';
$price = 0.0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $source = $_POST['Source'] ?? null;
    $destination = $_POST['Destination'] ?? null;
    $weight_g = $_POST['Weight'] ?? null;
    $is_bulky = $_POST['isBulky'] ?? false;
    $is_fresh = $_POST['isFresh'] ?? false;
    $id_user_assigned = $_POST['id_user_assigned'] ?? null;

    if (empty($source) || empty($destination) || empty($weight_g) || empty($id_user_assigned)) {
        $error_message = 'Incomplete data. Please go back and try again.';
    } else {
        
        // Dummy price calculation
        $base_fee = 5.0;
        $price_per_kg = 1.5;
        $price = $base_fee + (($weight_g / 1000) * $price_per_kg);
        
        try {
            $sql = "INSERT INTO DELIVERY (source, destination, weight_g, is_bulky, is_fresh, status, id_user_assigned, price) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                $source,
                $destination,
                (float)$weight_g,
                (bool)$is_bulky,
                (bool)$is_fresh,
                'assigned',
                (int)$id_user_assigned,
                $price
            ]);

            $new_delivery_id = $pdo->lastInsertId();
            $success_message = sprintf(
                'Delivery #%d has been successfully created and assigned! The delivery price is €%.2f.',
                $new_delivery_id,
                $price
            );

        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }

} else {
    $error_message = 'Invalid request method.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Confirmation - Sustainable Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main>
    <div class="container">
        <div class="card">
            <h2>Delivery Confirmation</h2>

            <?php if (!empty($error_message)): ?>
                <div data-message="error"><?php echo htmlspecialchars($error_message); ?></div>
                <p class="text-center mt-3"><a href="javascript:history.back()">Go Back</a></p>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div data-message="success"><?php echo htmlspecialchars($success_message); ?></div>
                <p class="text-center mt-3">You can now close this window. The deliverer has been notified.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Sustainable Delivery. All rights reserved.</p>
</footer>

</body>
</html>