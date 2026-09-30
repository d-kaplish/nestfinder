<?php
session_start();
include("db.php");
include("admin_sidebar.php");

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

// Fetch admin data
$query = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$user_id'");
$admin = mysqli_fetch_assoc($query);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    
    if (empty($name) || empty($email) || empty($contact)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email' AND user_id != '$user_id'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already exists";
        } else {
            // Handle profile picture
            $profile_pic = $admin['profile_picture'];
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $_FILES['profile_picture']['tmp_name']);
                finfo_close($finfo);
                
                if (in_array($mime_type, $allowed)) {
                    $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                    $new_filename = "admin_" . $user_id . "_" . time() . "." . $ext;
                    $upload_path = "uploads/profiles/" . $new_filename;
                    
                    if (!is_dir("uploads/profiles")) {
                        mkdir("uploads/profiles", 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        if ($admin['profile_picture'] && file_exists("uploads/profiles/" . $admin['profile_picture'])) {
                            @unlink("uploads/profiles/" . $admin['profile_picture']);
                        }
                        $profile_pic = $new_filename;
                    }
                } else {
                    $error = "Invalid file type";
                }
            }
            
            if (empty($error)) {
                $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, contact = ?, profile_picture = ? WHERE user_id = ?");
                mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $contact, $profile_pic, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    $message = "Profile updated successfully! <i class='fas fa-check-circle'></i>";
                    $query = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$user_id'");
                    $admin = mysqli_fetch_assoc($query);
                } else {
                    $error = "Failed to update profile";
                }
            }
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters";
    } elseif ($new_password != $confirm_password) {
        $error = "New passwords do not match";
    } else {
        if (password_verify($current_password, $admin['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'");
            $message = "Password changed successfully! <i class='fas fa-check-circle'></i>";
        } else {
            $error = "Current password is incorrect";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Profile | NestFinder</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    <?php include("admin_sidebar.php"); ?>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1><i class="fas fa-user-shield"></i> Admin Profile</h1>
            <p>Manage your administrator account <i class="fas fa-crown"></i></p>
        </div>
        
        <div class="profile-container-admin">
            <div class="profile-card">
                <?php if ($message): ?>
                    <div class="success-message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="auth-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Profile Picture -->
                <div class="profile-picture-section">
                    <div class="profile-picture-container">
                        <?php if ($admin['profile_picture'] && file_exists("uploads/profiles/" . $admin['profile_picture'])): ?>
                            <img src="uploads/profiles/<?php echo $admin['profile_picture']; ?>" alt="Profile Picture" class="profile-img">
                        <?php else: ?>
                            <div class="profile-img-placeholder admin-placeholder">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-role-badge">
                        <i class="fas fa-crown"></i> Administrator
                    </div>
                </div>
                
                <!-- Tabs -->
                <div class="profile-tabs">
                    <button class="tab-btn active" onclick="showTab('info')"><i class="fas fa-info-circle"></i> Profile Information</button>
                    <button class="tab-btn" onclick="showTab('password')"><i class="fas fa-key"></i> Change Password</button>
                </div>
                
                <!-- Profile Info Tab -->
                <div id="info-tab" class="tab-content active">
                    <form method="POST" enctype="multipart/form-data" class="profile-form">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Contact Number</label>
                            <input type="text" name="contact" value="<?php echo htmlspecialchars($admin['contact']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Profile Picture</label>
                            <input type="file" name="profile_picture" accept="image/*">
                            <small><i class="fas fa-info-circle"></i> Supported formats: JPG, PNG, GIF, WEBP</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Admin Since</label>
                            <input type="text" value="<?php echo date('d M Y', strtotime($admin['created_at'])); ?>" disabled>
                        </div>
                        
                        <button type="submit" name="update_profile" class="save-btn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
                
                <!-- Change Password Tab -->
                <div id="password-tab" class="tab-content">
                    <form method="POST" class="profile-form">
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-key"></i> New Password</label>
                            <input type="password" name="new_password" required>
                            <small><i class="fas fa-info-circle"></i> Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-check-circle"></i> Confirm New Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="save-btn">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}
</script>

</body>
</html>