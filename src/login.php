<?php
/*
 * login.php
 * Handles user login.
 */

require_once 'config.php'; // Includes PDO and starts session

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error_message = 'Please enter both login and password.';
    } else {
        try {
            // Find the user by login
            $stmt = $pdo->prepare("SELECT id_user, login, password FROM USER WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            // Verify password
            if ($user && password_verify($password, $user['password'])) {
                // Password is correct!
                // Store user info in session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_login'] = $user['login'];

                // Redirect to the dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                // Invalid login or password
                $error_message = 'Invalid login or password.';
            }

        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// Include the header
include 'header.php';
?>

<div class="form-wrapper">
    <form action="login.php" method="POST">
        <h2>Deliverer Login</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label for="login">Login</label>
            <input type="text" id="login" name="login" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
        <div class="form-links">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </form>
</div>

<?php
// Include the footer
include 'footer.php';
?>
