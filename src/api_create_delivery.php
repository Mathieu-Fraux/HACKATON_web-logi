<?php
/*
 * api_create_delivery.php
 * (FORMERLY API ENDPOINT)
 * * This page now shows a list of available deliverers for an external user
 * to choose from. It receives delivery details via GET parameters.
 */

// Include config for database connection
require_once 'config.php'; // This also includes header.php

// --- Validate Input ---
// Get parameters from the URL query string
$source = $_GET['Source'] ?? null;
$destination = $_GET['Destination'] ?? null;
$weight_g = $_GET['Weight'] ?? null;
$is_bulky = $_GET['isBulky'] ?? false;
$is_fresh = $_GET['isFresh'] ?? false;

$error_message = '';
$users = [];

// Check for required fields
if (empty($source) || empty($destination) || empty($weight_g)) {
    $error_message = 'Missing required delivery information (Source, Destination, or Weight).';
} else {
    // Fetch all users to display for selection
    // As requested, "for now, let's simply show all of our users"
    try {
        $stmt = $pdo->query("SELECT id_user, login, first_name, last_name, vehicle, location, range_km FROM USER");
        $users = $stmt->fetchAll();

        if (empty($users)) {
            $error_message = 'No deliverers are available at this time.';
        }

    } catch (PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}

// Include the header
include 'header.php';
?>

<div class="container">
    <h2>Assign a Delivery</h2>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php else: ?>
        
        <!-- Display Delivery Details -->
        <div class="delivery-item">
            <h3>Delivery Details:</h3>
            <p><strong>From:</strong> <?php echo htmlspecialchars($source); ?></p>
            <p><strong>To:</strong> <?php echo htmlspecialchars($destination); ?></p>
            <p><strong>Weight:</strong> <?php echo htmlspecialchars($weight_g); ?>g</p>
        </div>

        <h3>Choose a Deliverer:</h3>
        
        <form action="assign_delivery.php" method="POST">
            <!-- 
                Pass all the delivery details through as hidden fields 
                so the next page can process them.
            -->
            <input type="hidden" name="Source" value="<?php echo htmlspecialchars($source); ?>">
            <input type="hidden" name="Destination" value="<?php echo htmlspecialchars($destination); ?>">
            <input type="hidden" name="Weight" value="<?php echo htmlspecialchars($weight_g); ?>">
            <input type="hidden" name="isBulky" value="<?php echo htmlspecialchars($is_bulky); ?>">
            <input type="hidden" name="isFresh" value="<?php echo htmlspecialchars($is_fresh); ?>">

            <div class="user-selection-list">
                <?php foreach ($users as $user): ?>
                    <label class="user-choice">
                        <input type="radio" name="id_user_assigned" value="<?php echo $user['id_user']; ?>" required>
                        <div class="user-details">
                            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong> (<?php echo htmlspecialchars($user['login']); ?>)
                            <br>
                            <small>
                                Vehicle: <?php echo htmlspecialchars($user['vehicle'] ?? 'N/A'); ?> | 
                                Based in: <?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?> |
                                Range: <?php echo htmlspecialchars($user['range_km']); ?> KM
                            </small>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" style="margin-top: 20px;">Confirm and Create Delivery</button>
        </form>

    <?php endif; ?>
</div>

<!-- Some quick extra styles for the selection list -->
<style>
.user-selection-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.user-choice {
    display: flex;
    align-items: center;
    padding: 15px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 5px;
    cursor: pointer;
}
.user-choice:hover {
    background-color: #f1f1f1;
}
.user-choice input[type="radio"] {
    margin-right: 15px;
    transform: scale(1.2);
}
.user-details {
    line-height: 1.4;
}
.user-details small {
    color: #555;
}
</style>

<?php
// Include the footer
include 'footer.php';
?>

