<?php
/*
 * dashboard.php
 * Main page for logged-in deliverers.
 */

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$upcoming_deliveries = [];
$past_deliveries = [];

try {
    $stmt_upcoming = $pdo->prepare(
        "SELECT * FROM DELIVERY 
         WHERE id_user_assigned = ? 
         AND (status = 'assigned' OR status = 'in_progress')
         ORDER BY id_delivery DESC"
    );
    $stmt_upcoming->execute([$user_id]);
    $upcoming_deliveries = $stmt_upcoming->fetchAll();

    $stmt_past = $pdo->prepare(
        "SELECT * FROM DELIVERY 
         WHERE id_user_assigned = ? 
         AND (status = 'completed' OR status = 'cancelled')
         ORDER BY id_delivery DESC"
    );
    $stmt_past->execute([$user_id]);
    $past_deliveries = $stmt_past->fetchAll();

} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}

include 'header.php';
?>

<div class="dashboard-welcome">
    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_login']); ?>!</h2>
    <p>Manage your deliveries and track your progress</p>
</div>

<?php if (!empty($error_message)): ?>
    <div data-message="error"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<section>
    <h3>Upcoming Deliveries</h3>
    <?php if (empty($upcoming_deliveries)): ?>
        <div class="empty-state">
            <p>You have no upcoming deliveries.</p>
        </div>
    <?php else: ?>
        <ul>
            <?php foreach ($upcoming_deliveries as $delivery): ?>
                <li data-delivery-id="<?php echo $delivery['id_delivery']; ?>">
                    <h4>Delivery #<?php echo $delivery['id_delivery']; ?></h4>
                    <p><strong>From:</strong> <?php echo htmlspecialchars($delivery['source']); ?></p>
                    <p><strong>To:</strong> <?php echo htmlspecialchars($delivery['destination']); ?></p>
                    <p><strong>Weight:</strong> <?php echo $delivery['weight_g']; ?>g</p>
                    <p><strong>Price:</strong> €<?php echo number_format($delivery['price'], 2); ?></p>
                    <p><strong>Status:</strong> 
                        <span data-status="<?php echo strtolower(htmlspecialchars($delivery['status'])); ?>">
                            <?php echo htmlspecialchars($delivery['status']); ?>
                        </span>
                    </p>
                    
                    <form action="update_delivery_status.php" method="POST">
                        <input type="hidden" name="id_delivery" value="<?php echo $delivery['id_delivery']; ?>">
                        <div class="form-group">
                            <label for="status-<?php echo $delivery['id_delivery']; ?>">Change Status:</label>
                            <select name="status" id="status-<?php echo $delivery['id_delivery']; ?>">
                                <option value="assigned" <?php if($delivery['status'] == 'assigned') echo 'selected'; ?>>Assigned</option>
                                <option value="in_progress" <?php if($delivery['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                <option value="completed" <?php if($delivery['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                                <option value="cancelled" <?php if($delivery['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit">Update</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<section>
    <h3>Past Deliveries</h3>
    <?php if (empty($past_deliveries)): ?>
        <div class="empty-state">
            <p>You have no past deliveries.</p>
        </div>
    <?php else: ?>
        <ul>
            <?php foreach ($past_deliveries as $delivery): ?>
                <li data-delivery-id="<?php echo $delivery['id_delivery']; ?>">
                    <h4>Delivery #<?php echo $delivery['id_delivery']; ?></h4>
                    <p><strong>From:</strong> <?php echo htmlspecialchars($delivery['source']); ?></p>
                    <p><strong>To:</strong> <?php echo htmlspecialchars($delivery['destination']); ?></p>
                    <p><strong>Weight:</strong> <?php echo $delivery['weight_g']; ?>g</p>
                    <p><strong>Price:</strong> €<?php echo number_format($delivery['price'], 2); ?></p>
                    <p><strong>Status:</strong> 
                        <span data-status="<?php echo strtolower(htmlspecialchars($delivery['status'])); ?>">
                            <?php echo htmlspecialchars($delivery['status']); ?>
                        </span>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>