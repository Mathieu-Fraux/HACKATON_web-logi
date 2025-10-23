<?php
/*
 * assign_delivery.php
 * Handles the form submission from api_create_delivery.php (deliverer selection).
 * This script creates the final delivery record with an assigned user.
 */

require_once 'config.php'; // Includes PDO and starts session

$error_message = '';
$success_message = '';
$price = 0.0;

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get all delivery details from hidden fields
    $source = $_POST['Source'] ?? null;
    $destination = $_POST['Destination'] ?? null;
    $weight_g = $_POST['Weight'] ?? null;
    $is_bulky = $_POST['isBulky'] ?? false;
    $is_fresh = $_POST['isFresh'] ?? false;
    
    // Get the selected deliverer
    $id_user_assigned = $_POST['id_user_assigned'] ?? null;

    // Check for required fields
    if (empty($source) || empty($destination) || empty($weight_g) || empty($id_user_assigned)) {
        $error_message = 'Incomplete data. Please go back and try again.';
    } else {
        
        // --- DUMMY PRICE CALCULATION ---
        // You can replace this with a real calculation (e.g., using Google Maps API + weight)
        $base_fee = 5.0;
        $price_per_kg = 1.5;
        $price = $base_fee + (($weight_g / 1000) * $price_per_kg);
        
        // --- Insert into Database ---
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
                'assigned', // Status is 'assigned' because a user was selected
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
    // Not a POST request
    $error_message = 'Invalid request method.';
}

// Include the header
include 'header.php';
?>

<div class="container">
    <h2>Delivery Confirmation</h2>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <p><a href="javascript:history.back()">Go Back</a></p>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
        <p>This confirmation page would be shown to the external user. You can now close this window.</p>
        <!-- 
            Per your original spec: "redirect to the external website with a /POST"
            This is technically not possible with a simple redirect.
            You could provide a link back, e.g.:
            <a href="http://external-website.com/confirmation.php?delivery_id=...&price=...">Return to site</a>
            For now, we just show this message.
        -->
    <?php endif; ?>

</div>

<?php
// Include the footer
include 'footer.php';
?>
