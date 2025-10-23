<?php
/*
 * api_create_delivery.php
 * Shows a list of available deliverers for an external user to choose from.
 * Receives delivery details via GET parameters.
 */

require_once 'header_external.php';

$source = $_GET['Source'] ?? null;
$destination = $_GET['Destination'] ?? null;
$weight_g = $_GET['Weight'] ?? null;
$is_bulky = $_GET['isBulky'] ?? false;
$is_fresh = $_GET['isFresh'] ?? false;

$error_message = '';
$users = [];

if (empty($source) || empty($destination) || empty($weight_g)) {
    $error_message = 'Missing required delivery information (Source, Destination, or Weight).';
} else {
    try {
        $stmt = $pdo->query("SELECT id_user, login, first_name, last_name, vehicle, location, range_km FROM USER ORDER BY first_name, last_name");
        $users = $stmt->fetchAll();

        if (empty($users)) {
            $error_message = 'No deliverers are available at this time.';
        }
    } catch (PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}

?>

<div class="container">
    <h2>Assign a Delivery</h2>

    <?php if (!empty($error_message)): ?>
        <div data-message="error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php else: ?>
        
        <div data-delivery-details>
            <h3>Delivery Details:</h3>
            <p><strong>From:</strong> <?php echo htmlspecialchars($source); ?></p>
            <p><strong>To:</strong> <?php echo htmlspecialchars($destination); ?></p>
            <p><strong>Weight:</strong> <?php echo htmlspecialchars($weight_g); ?>g</p>
        </div>

        <h3>Choose a Deliverer:</h3>
        
        <form action="assign_delivery.php" method="POST">
            <input type="hidden" name="Source" value="<?php echo htmlspecialchars($source); ?>">
            <input type="hidden" name="Destination" value="<?php echo htmlspecialchars($destination); ?>">
            <input type="hidden" name="Weight" value="<?php echo htmlspecialchars($weight_g); ?>">
            <input type="hidden" name="isBulky" value="<?php echo htmlspecialchars($is_bulky); ?>">
            <input type="hidden" name="isFresh" value="<?php echo htmlspecialchars($is_fresh); ?>">

            <div data-user-list>
                <?php foreach ($users as $user): ?>
                    <label data-user-option>
                        <input type="radio" name="id_user_assigned" value="<?php echo $user['id_user']; ?>" required>
                        <div>
                            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                            <small>(<?php echo htmlspecialchars($user['login']); ?>)</small>
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

            <button type="submit">Confirm and Create Delivery</button>
        </form>

    <?php endif; ?>
</div>

<?php include 'footer_external.php'; ?>