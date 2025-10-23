<?php
/*
 * register.php
 * Handles new user registration.
 */

require_once 'config.php'; // Includes PDO and starts session

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data (with null coalescing operator for safety)
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

    // --- Validation ---
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
        // Validation passed, try to insert into DB
        try {
            // Check if login or email already exists
            $stmt = $pdo->prepare("SELECT 1 FROM USER WHERE login = ? OR email = ?");
            $stmt->execute([$login, $email]);
            if ($stmt->fetch()) {
                $error_message = 'Login or email already taken.';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $sql = "INSERT INTO USER (login, password, first_name, last_name, email, phone, vehicle, location, range_km) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                $stmt->execute([
                    $login,
                    $hashed_password,
                    $first_name,
                    $last_name,
                    $email,
                    $phone, // Can be null
                    $vehicle, // Can be null
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

// Include the header
include 'header.php';
?>

<div class="form-wrapper" style="max-width: 600px;"> <!-- Wider form for registration -->
    <form action="register.php" method="POST">
        <h2>Create Deliverer Account</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Required Fields -->
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
            <label for="location">Full Address* (e.g., 88 allées Jean Jaurès 31000 Toulouse)</label>
            <input type="text" id="location" name="location" required>
        </div>
         <div class="form-group">
            <label for="range_km">Max Delivery Range (KM)* (Max 100)</label>
            <input type="number" id="range_km" name="range_km" max="100" min="1" required>
        </div>

        <!-- Optional Fields -->
        <div class="form-group">
            <label for="phone">Phone (Optional)</label>
            <input type="tel" id="phone" name="phone">
        </div>
        <div class="form-group">
            <label for="vehicle">Vehicle (Optional)</label>
            <input type="text" id="vehicle" name="vehicle" placeholder="e.g., Renault Clio, Cargo Bike">
        </div>

        <button type="submit">Register</button>
        <div class="form-links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </form>
</div>

<?php
// Include the footer
include 'footer.php';
?>
