<?php
/*
 * register.php
 * Handles new user registration.
 */

require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'] ?? null;
    $password = $_POST['password'] ?? null;
    $password_confirm = $_POST['password_confirm'] ?? null;
    $first_name = $_POST['first_name'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $vehicle = $_POST['vehicle'] ?? null;
    $location = $_POST['location'] ?? null;
    $range_km = $_POST['range_km'] ?? null;

    if (empty($login) || empty($password) || empty($password_confirm) || empty($first_name) || empty($last_name) || empty($email) || empty($location) || empty($range_km)) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $password_confirm) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email address.';
    } elseif ($range_km > 100) {
        $error_message = 'Range cannot be more than 100 KM.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT 1 FROM USER WHERE login = ? OR email = ?");
            $stmt->execute([$login, $email]);
            if ($stmt->fetch()) {
                $error_message = 'Login or email already taken.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO USER (login, password, first_name, last_name, email, phone, vehicle, location, range_km) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                $stmt->execute([
                    $login,
                    $hashed_password,
                    $first_name,
                    $last_name,
                    $email,
                    $phone,
                    $vehicle,
                    $location,
                    $range_km
                ]);

                $success_message = 'Registration successful! You can now <a href="login.php">login</a>.';
            }
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="container">
    <form action="register.php" method="POST">
        <h2>Create Deliverer Account</h2>

        <?php if (!empty($error_message)): ?>
            <div data-message="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div data-message="success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label for="login">Login*</label>
            <input type="text" id="login" name="login" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password* (min 8 chars)</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Confirm Password*</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        
        <div class="form-group">
            <label for="first_name">First Name*</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name*</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email*</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="location">Full Address*</label>
            <input type="text" id="location" name="location" placeholder="e.g., 88 allées Jean Jaurès 31000 Toulouse" required>
        </div>
        
        <div class="form-group">
            <label for="range_km">Max Delivery Range (KM)* (Max 100)</label>
            <input type="number" id="range_km" name="range_km" max="100" min="1" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone (Optional)</label>
            <input type="tel" id="phone" name="phone">
        </div>
        
        <div class="form-group">
            <label for="vehicle">Vehicle (Optional)</label>
            <input type="text" id="vehicle" name="vehicle" placeholder="e.g., Renault Clio, Cargo Bike">
        </div>

        <button type="submit">Register</button>
        
        <div class="text-center mt-3">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>