<?php
session_start();
include "db.php";

// If already logged in, redirect to appropriate page
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}

$msg = "";
$error = "";

if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($contact) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email already exists
        $check_stmt = mysqli_prepare($conn, "SELECT email FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, contact, password, is_admin) VALUES (?, ?, ?, ?, 0)");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $contact, $hashed_password);
            
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Signup successful! You can now login.";
                // Clear form data
                $name = $email = $contact = "";
            } else {
                $error = "Signup failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup | NestFinder</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2><i class="fas fa-user-plus"></i> Create Account</h2>
            <p>Join NestFinder today! <i class="fas fa-home"></i></p>
        </div>

        <?php if ($msg): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
                <br><br>
                <a href="login.php" style="color: #155724; font-weight: bold;">Click here to login <i class="fas fa-arrow-right"></i></a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="auth-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="signupForm">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Full Name</label>
                <input type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-phone"></i> Contact Number</label>
                <input type="text" name="contact" value="<?php echo isset($contact) ? htmlspecialchars($contact) : ''; ?>" placeholder="Enter your mobile number" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Create a password (min 6 characters)" required>
                    <i class="fas fa-eye" id="togglePassword" onclick="togglePassword('password', 'togglePassword')"></i>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
                    <i class="fas fa-eye" id="toggleConfirmPassword" onclick="togglePassword('confirm_password', 'toggleConfirmPassword')"></i>
                </div>
            </div>

            <button type="submit" name="signup" class="auth-btn">
                <i class="fas fa-user-plus"></i> Sign Up
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login <i class="fas fa-sign-in-alt"></i></a></p>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId, iconId) {
        var passwordField = document.getElementById(fieldId);
        var toggleIcon = document.getElementById(iconId);
        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleIcon.classList.remove("fa-eye");
            toggleIcon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            toggleIcon.classList.remove("fa-eye-slash");
            toggleIcon.classList.add("fa-eye");
        }
    }
    
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        var password = document.getElementById('password').value;
        var confirm = document.getElementById('confirm_password').value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            return false;
        }
    });
</script>

</body>
</html>