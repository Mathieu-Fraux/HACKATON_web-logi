<?php
/*
 * login.php
 * Handles user login.
 */

require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error_message = 'Please enter both login and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_user, login, password FROM USER WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_login'] = $user['login'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error_message = 'Invalid login or password.';
            }
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sustainable Delivery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main>
    <div class="container">
        <form action="login.php" method="POST">
            <h2>Deliverer Login</h2>

            <div class="form-group">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
            
            <div class="text-center mt-3">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </form>
    </div>
</main>

<?php if (!empty($error_message)): ?>
    <div class="toast toast-error">
        <span class="toast-icon">✕</span>
        <span class="toast-message"><?php echo htmlspecialchars($error_message); ?></span>
    </div>
<?php endif; ?>

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