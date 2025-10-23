<?php
/*
 * dashboard.php
 * Driver marketplace: Available Deliveries (to claim) + My Deliveries (assigned work)
 */

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$available_deliveries = [];
$my_active_deliveries = [];
$my_past_deliveries = [];

// Get any flash messages from claiming or status updates
$claim_success = $_SESSION['claim_success'] ?? '';
$claim_error = $_SESSION['claim_error'] ?? '';
$update_success = $_SESSION['update_success'] ?? '';
$update_error = $_SESSION['update_error'] ?? '';
unset($_SESSION['claim_success'], $_SESSION['claim_error'], $_SESSION['update_success'], $_SESSION['update_error']);

try {
    // Fetch available deliveries (unclaimed, status = available)
    $stmt_available = $pdo->prepare(
        "SELECT id_delivery, source, destination, weight_g, is_bulky, is_fresh, price, created_at
         FROM DELIVERY 
         WHERE status = 'available' AND id_user_assigned IS NULL
         ORDER BY created_at ASC"
    );
    $stmt_available->execute();
    $available_deliveries = $stmt_available->fetchAll();

    // Fetch driver's active deliveries (assigned or in_progress)
    $stmt_active = $pdo->prepare(
        "SELECT * FROM DELIVERY 
         WHERE id_user_assigned = ? 
         AND (status = 'assigned' OR status = 'in_progress')
         ORDER BY claimed_at DESC, id_delivery DESC"
    );
    $stmt_active->execute([$user_id]);
    $my_active_deliveries = $stmt_active->fetchAll();

    // Fetch driver's past deliveries (completed or cancelled)
    $stmt_past = $pdo->prepare(
        "SELECT * FROM DELIVERY 
         WHERE id_user_assigned = ? 
         AND (status = 'completed' OR status = 'cancelled')
         ORDER BY updated_at DESC, id_delivery DESC"
    );
    $stmt_past->execute([$user_id]);
    $my_past_deliveries = $stmt_past->fetchAll();

} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}

// Helper function to format time ago
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sustainable Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main>

<div class="dashboard-welcome">
    <div class="dashboard-welcome-content">
        <div>
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_login']); ?>!</h2>
            <p>Claim available deliveries or manage your active work</p>
        </div>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</div>

<?php if (!empty($error_message)): ?>
    <div class="toast toast-error">
        <span class="toast-icon">✕</span>
        <span class="toast-message"><?php echo htmlspecialchars($error_message); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($claim_success)): ?>
    <div class="toast toast-success">
        <span class="toast-icon">✓</span>
        <span class="toast-message"><?php echo htmlspecialchars($claim_success); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($claim_error)): ?>
    <div class="toast toast-error">
        <span class="toast-icon">✕</span>
        <span class="toast-message"><?php echo htmlspecialchars($claim_error); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($update_success)): ?>
    <div class="toast toast-success">
        <span class="toast-icon">✓</span>
        <span class="toast-message"><?php echo htmlspecialchars($update_success); ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($update_error)): ?>
    <div class="toast toast-error">
        <span class="toast-icon">✕</span>
        <span class="toast-message"><?php echo htmlspecialchars($update_error); ?></span>
    </div>
<?php endif; ?>

<!-- AVAILABLE DELIVERIES MARKETPLACE -->
<section class="marketplace-section">
    <h3>📦 Available Deliveries</h3>
    
    <?php if (empty($available_deliveries)): ?>
        <div class="empty-state">
            <p>No deliveries available right now. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="marketplace-grid">
            <?php foreach ($available_deliveries as $delivery): ?>
                <div class="marketplace-card">
                    <div class="marketplace-header">
                        <span class="delivery-id">#<?php echo $delivery['id_delivery']; ?></span>
                        <span class="time-posted"><?php echo timeAgo($delivery['created_at']); ?></span>
                    </div>
                    
                    <div class="route-info">
                        <div class="route-point">
                            <strong>From:</strong>
                            <span><?php echo htmlspecialchars($delivery['source']); ?></span>
                        </div>
                        <div class="route-arrow">→</div>
                        <div class="route-point">
                            <strong>To:</strong>
                            <span><?php echo htmlspecialchars($delivery['destination']); ?></span>
                        </div>
                    </div>
                    
                    <div class="delivery-details">
                        <div class="detail-item">
                            <span class="detail-label">Weight:</span>
                            <span class="detail-value"><?php echo number_format($delivery['weight_g']); ?>g</span>
                        </div>
                        <?php if ($delivery['is_bulky']): ?>
                            <span class="badge badge-warning">Bulky</span>
                        <?php endif; ?>
                        <?php if ($delivery['is_fresh']): ?>
                            <span class="badge badge-info">Fresh</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="marketplace-footer">
                        <div class="price-display">€<?php echo number_format($delivery['price'], 2); ?></div>
                        <form action="claim_delivery.php" method="POST" class="claim-form">
                            <input type="hidden" name="id_delivery" value="<?php echo $delivery['id_delivery']; ?>">
                            <button type="submit" class="btn-claim">Claim Delivery</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- MY DELIVERIES SPLIT VIEW -->
<div class="dashboard-split-view">
    <section class="dashboard-section">
        <h3>🚚 My Active Deliveries</h3>
        <?php if (empty($my_active_deliveries)): ?>
            <div class="empty-state">
                <p>You have no active deliveries. Claim one from the marketplace above!</p>
            </div>
        <?php else: ?>
            <ul class="delivery-list">
                <?php foreach ($my_active_deliveries as $delivery): ?>
                    <li class="delivery-card">
                        <div class="delivery-header">
                            <h4>Delivery #<?php echo $delivery['id_delivery']; ?></h4>
                            <span data-status="<?php echo strtolower(htmlspecialchars($delivery['status'])); ?>">
                                <?php echo htmlspecialchars($delivery['status']); ?>
                            </span>
                        </div>
                        
                        <div class="delivery-info">
                            <p><strong>From:</strong> <?php echo htmlspecialchars($delivery['source']); ?></p>
                            <p><strong>To:</strong> <?php echo htmlspecialchars($delivery['destination']); ?></p>
                            <p><strong>Weight:</strong> <?php echo number_format($delivery['weight_g']); ?>g
                                <?php if ($delivery['is_bulky']): ?><span class="badge badge-sm">Bulky</span><?php endif; ?>
                                <?php if ($delivery['is_fresh']): ?><span class="badge badge-sm">Fresh</span><?php endif; ?>
                            </p>
                            <p><strong>Price:</strong> €<?php echo number_format($delivery['price'], 2); ?></p>
                            <?php if ($delivery['claimed_at']): ?>
                                <p><strong>Claimed:</strong> <?php echo date('M j, Y g:i A', strtotime($delivery['claimed_at'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <form action="update_delivery_status.php" method="POST" class="status-update-form">
                            <input type="hidden" name="id_delivery" value="<?php echo $delivery['id_delivery']; ?>">
                            <select name="status" id="status-<?php echo $delivery['id_delivery']; ?>">
                                <option value="assigned" <?php if($delivery['status'] == 'assigned') echo 'selected'; ?>>Assigned</option>
                                <option value="in_progress" <?php if($delivery['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <button type="submit">Update Status</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="dashboard-section">
        <h3>📋 Past Deliveries</h3>
        <?php if (empty($my_past_deliveries)): ?>
            <div class="empty-state">
                <p>No completed deliveries yet.</p>
            </div>
        <?php else: ?>
            <ul class="delivery-list">
                <?php foreach ($my_past_deliveries as $delivery): ?>
                    <li class="delivery-card">
                        <div class="delivery-header">
                            <h4>Delivery #<?php echo $delivery['id_delivery']; ?></h4>
                            <span data-status="<?php echo strtolower(htmlspecialchars($delivery['status'])); ?>">
                                <?php echo htmlspecialchars($delivery['status']); ?>
                            </span>
                        </div>
                        
                        <div class="delivery-info">
                            <p><strong>From:</strong> <?php echo htmlspecialchars($delivery['source']); ?></p>
                            <p><strong>To:</strong> <?php echo htmlspecialchars($delivery['destination']); ?></p>
                            <p><strong>Weight:</strong> <?php echo number_format($delivery['weight_g']); ?>g</p>
                            <p><strong>Price:</strong> €<?php echo number_format($delivery['price'], 2); ?></p>
                            <p><strong>Completed:</strong> <?php echo date('M j, Y', strtotime($delivery['updated_at'])); ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

</main>

<footer>
    <p>&copy; <?php echo date('Y'); ?> Sustainable Delivery. All rights reserved.</p>
</footer>

<script>
// Auto-dismiss toast notifications after 4 seconds
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        // Trigger fade in
        setTimeout(() => {
            toast.classList.add('toast-show');
        }, 100);
        
        // Trigger fade out and remove
        setTimeout(() => {
            toast.classList.remove('toast-show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 4000);
    });
});
</script>

</body>
</html>