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

include 'header.php';
?>

<div class="container">
    <form action="login.php" method="POST">
        <h2>Deliverer Login</h2>

        <?php if (!empty($error_message)): ?>
            <div data-message="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

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

<?php include 'footer.php'; ?>