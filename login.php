<?php
session_start();
include "db.php";

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}

$error = "";

function check_lockout($conn, $email) {
    $query = "SELECT failed_attempts, last_failed FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['failed_attempts'] >= 5) {
            $lockout_time = strtotime($row['last_failed']);
            if (time() - $lockout_time < 900) {
                return "Account locked. Try again after 15 minutes.";
            } else {
                mysqli_query($conn, "UPDATE users SET failed_attempts = 0 WHERE email='$email'");
                return false;
            }
        }
    }
    return false;
}

function record_failed_attempt($conn, $email) {
    mysqli_query($conn, "UPDATE users SET failed_attempts = failed_attempts + 1, last_failed = NOW() WHERE email='$email'");
}

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = mysqli_real_escape_string($conn, $_COOKIE['remember_token']);
    $query = "SELECT * FROM users WHERE remember_token='$token'";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['is_admin'] = (int)$row['is_admin'];
        
        if ($_SESSION['is_admin'] == 1) {
            header("Location: admin_dashboard.php");
            exit();
        } else {
            header("Location: index.php");
            exit();
        }
    } else {
        setcookie('remember_token', '', time() - 3600, "/");
    }
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? true : false;
    
    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else {
        $lockout_msg = check_lockout($conn, $email);
        if ($lockout_msg) {
            $error = $lockout_msg;
        } else {
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=?");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                
                if (password_verify($password, $row['password'])) {
                    mysqli_query($conn, "UPDATE users SET failed_attempts = 0 WHERE email='$email'");
                    
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['name'] = $row['name'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['is_admin'] = $row['is_admin'];
                    
                    if ($remember_me) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + (86400 * 30), "/");
                        mysqli_query($conn, "UPDATE users SET remember_token='$token' WHERE user_id='{$row['user_id']}'");
                    }
                    
                    if ($row['is_admin'] == 1) {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                    
                } else {
                    record_failed_attempt($conn, $email);
                    $error = "Incorrect password";
                }
            } else {
                $error = "User not found";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | NestFinder</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var toggleIcon = document.getElementById("toggleIcon");
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
        
        function validateForm() {
            var email = document.forms["loginForm"]["email"].value.trim();
            var password = document.forms["loginForm"]["password"].value;
            if (email === "" || password === "") {
                alert("Please fill all required fields");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2><i class="fas fa-user-lock"></i> Login to NestFinder</h2>
            <p>Welcome back! <i class="fas fa-smile-wink"></i></p>
        </div>

        <form method="POST" name="loginForm" onsubmit="return validateForm()">
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-key"></i> Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    <i class="fas fa-eye" id="toggleIcon" onclick="togglePassword()"></i>
                </div>
            </div>
             
            <div class="checkbox-group">
                <label><input type="checkbox" name="remember_me"> Remember Me</label>
            </div>

            <button type="submit" name="login" class="auth-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <?php if ($error): ?>
            <div class="auth-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="auth-footer">
            <p>Don't have an account? <a href="signup.php">Sign up <i class="fas fa-arrow-right"></i></a></p>
        </div>
    </div>
</div>

</body>
</html>