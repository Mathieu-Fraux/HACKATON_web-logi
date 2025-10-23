<?php
/*
 * dashboard.php
 * Main page for logged-in deliverers.
 */

require_once 'config.php'; // Includes PDO and starts session

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$upcoming_deliveries = [];
$past_deliveries = [];

// Fetch deliveries assigned to this user
try {
    // Upcoming: 'assigned' or 'in_progress'
    $stmt_upcoming = $pdo->prepare(
        "SELECT * FROM DELIVERY 
         WHERE id_user_assigned = ? 
         AND (status = 'assigned' OR status = 'in_progress')"
    );
    $stmt_upcoming->execute([$user_id]);
    $upcoming_deliveries = $stmt_upcoming->fetchAll();

    // Past: 'completed' or 'cancelled'
    $stmt_past = $pdo->prepare(
        "SELECT * FROM DELIVERY 
         WHERE id_user_assigned = ? 
         AND (status = 'completed' OR status = 'cancelled')"
    );
    $stmt_past->execute([$user_id]);
    $past_deliveries = $stmt_past->fetchAll();

} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}

// Include the header
include 'header.php';
?>

<div class="container">
    <h2>My Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_login']); ?>!</p>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Section for upcoming deliveries -->
    <section class="delivery-section">
        <h3>Upcoming Deliveries</h3>
        <?php if (empty($upcoming_deliveries)): ?>
            <p>You have no upcoming deliveries.</p>
        <?php else: ?>
            <ul class="delivery-list">
                <?php foreach ($upcoming_deliveries as $delivery): ?>
                    <li class="delivery-item">
                        <h3>Delivery #<?php echo $delivery['id_delivery']; ?></h3>
                        <p><strong>From:</strong> <?php echo htmlspecialchars($delivery['source']); ?></p>
                        <p><strong>To:</strong> <?php echo htmlspecialchars($delivery['destination']); ?></p>
                        <p><strong>Weight:</strong> <?php echo $delivery['weight_g']; ?>g</p>
                        <p><strong>Price:</strong> €<?php echo $delivery['price']; ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status status-<?php echo strtolower(htmlspecialchars($delivery['status'])); ?>">
                                <?php echo htmlspecialchars($delivery['status']); ?>
                            </span>
                        </p>
                        
                        <!-- Form to update status -->
                        <form action="update_delivery_status.php" method="POST" class="status-form">
                            <input type="hidden" name="id_delivery" value="<?php echo $delivery['id_delivery']; ?>">
                            <label for="status-<?php echo $delivery['id_delivery']; ?>">Change Status:</label>
                            <select name="status" id="status-<?php echo $delivery['id_delivery']; ?>">
                                <option value="assigned" <?php if($delivery['status'] == 'assigned') echo 'selected'; ?>>Assigned</option>
                                <option value="in_progress" <?php if($delivery['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                <option value="completed" <?php if($delivery['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                                <option value="cancelled" <?php if($delivery['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Section for past deliveries -->
    <section class="delivery-section">
        <h3>Past Deliveries</h3>
        <?php if (empty($past_deliveries)): ?>
            <p>You have no past deliveries.</p>
        <?php else: ?>
            <ul class="delivery-list">
                <?php foreach ($past_deliveries as $delivery): ?>
                    <li class="delivery-item">
                        <h3>Delivery #<?php echo $delivery['id_delivery']; ?></h3>
                        <p><strong>From:</strong> <?php echo htmlspecialchars($delivery['source']); ?></p>
                        <p><strong>To:</strong> <?php echo htmlspecialchars($delivery['destination']); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status status-<?php echo strtolower(htmlspecialchars($delivery['status'])); ?>">
                                <?php echo htmlspecialchars($delivery['status']); ?>
                            </span>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

</div>

<?php
// Include the footer
include 'footer.php';
?>
