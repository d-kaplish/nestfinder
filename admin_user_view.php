<?php
session_start();
include("db.php");
include("admin_sidebar.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get user details
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($userQuery);

if (!$user) {
    header("Location: admin_users.php");
    exit();
}

// Get user's properties
$propertiesQuery = mysqli_query($conn, "SELECT * FROM properties WHERE user_id = $user_id ORDER BY id DESC");

// Get user's shortlisted properties
$shortlistQuery = mysqli_query($conn, 
    "SELECT p.* FROM properties p 
     JOIN shortlist s ON p.id = s.property_id 
     WHERE s.user_id = $user_id"
);

$propertyCount = mysqli_num_rows($propertiesQuery);
$shortlistCount = mysqli_num_rows($shortlistQuery);

// Delete property action
if (isset($_GET['delete_property'])) {
    $propId = intval($_GET['delete_property']);
    
    // Delete images from pictures table
    $pics = mysqli_query($conn, "SELECT file_name FROM pictures WHERE property_id='$propId'");
    while ($pic = mysqli_fetch_assoc($pics)) {
        @unlink("uploads/".$pic['file_name']);
    }
    mysqli_query($conn, "DELETE FROM pictures WHERE property_id='$propId'");
    
    // Delete main image
    $prop = mysqli_query($conn, "SELECT image FROM properties WHERE id='$propId'");
    if ($p = mysqli_fetch_assoc($prop)) {
        @unlink("uploads/".$p['image']);
    }
    
    // Delete property
    mysqli_query($conn, "DELETE FROM properties WHERE id='$propId'");
    
    header("Location: admin_user_view.php?id=$user_id");
    exit();
}

// Delete user action
if (isset($_GET['delete_user']) && $user_id != $_SESSION['user_id']) {
    // Delete all user's properties first
    $userProps = mysqli_query($conn, "SELECT id FROM properties WHERE user_id = $user_id");
    while ($prop = mysqli_fetch_assoc($userProps)) {
        // Delete property images
        $pics = mysqli_query($conn, "SELECT file_name FROM pictures WHERE property_id='{$prop['id']}'");
        while ($pic = mysqli_fetch_assoc($pics)) {
            @unlink("uploads/".$pic['file_name']);
        }
        mysqli_query($conn, "DELETE FROM pictures WHERE property_id='{$prop['id']}'");
        
        // Delete main image
        $mainImg = mysqli_query($conn, "SELECT image FROM properties WHERE id='{$prop['id']}'");
        if ($img = mysqli_fetch_assoc($mainImg)) {
            @unlink("uploads/".$img['image']);
        }
    }
    
    // Delete user's properties
    mysqli_query($conn, "DELETE FROM properties WHERE user_id = $user_id");
    
    // Delete user's shortlists
    mysqli_query($conn, "DELETE FROM shortlist WHERE user_id = $user_id");
    
    // Delete user
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
    
    header("Location: admin_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Details | Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    
    <?php include("admin_sidebar.php"); ?>
    
    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <div class="header-left">
                <a href="admin_users.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <h1>User Details</h1>
            </div>
            <?php if($user_id != $_SESSION['user_id']): ?>
                <a href="?delete_user=1" class="delete-user-btn" onclick="return confirm('Delete this user? All properties and shortlists will be deleted.')">
                    <i class="fas fa-trash"></i> Delete User
                </a>
            <?php endif; ?>
        </div>
        
        <!-- User Info Card -->
        <div class="user-info-card">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <div class="detail-row">
                    <span class="detail-label">User ID:</span>
                    <span class="detail-value"><?php echo $user['user_id']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo $user['email']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Contact:</span>
                    <span class="detail-value"><?php echo $user['contact']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Joined:</span>
                    <span class="detail-value"><?php echo date('d M Y, h:i A', strtotime($user['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Role:</span>
                    <span class="role-badge <?php echo $user['is_admin'] == 1 ? 'admin' : 'user'; ?>">
                        <?php echo $user['is_admin'] == 1 ? 'Administrator' : 'Regular User'; ?>
                    </span>
                </div>
            </div>
            <div class="user-stats">
                <div class="stat-box">
                    <i class="fas fa-building"></i>
                    <div class="stat-number"><?php echo $propertyCount; ?></div>
                    <div class="stat-text">Properties Listed</div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-heart"></i>
                    <div class="stat-number"><?php echo $shortlistCount; ?></div>
                    <div class="stat-text">Properties Shortlisted</div>
                </div>
            </div>
        </div>
        
        <!-- User's Properties -->
        <div class="recent-section">
            <div class="section-header">
                <h2><i class="fas fa-building"></i> Properties Listed by <?php echo $user['email']; ?></h2>
            </div>
            
            <?php if($propertyCount > 0): ?>
                <div class="property-grid">
                    <?php while($prop = mysqli_fetch_assoc($propertiesQuery)): ?>
                        <div class="property-card">
                            <img src="uploads/<?php echo $prop['image']; ?>" class="property-thumb" alt="<?php echo $prop['location']; ?>">
                            <div class="property-info">
                                <h4><?php echo $prop['location']; ?></h4>
                                <p class="property-price">₹<?php echo indianCurrency($prop['price']); ?></p>
                                <p class="property-meta"><?php echo $prop['rooms']; ?> • <?php echo $prop['property_type']; ?> • <?php echo $prop['type']; ?></p>
                                <div class="property-actions">
                                    <a href="admin_property_view.php?id=<?php echo $prop['id']; ?>" class="action-btn view-btn">View</a>
                                    <a href="?delete_property=<?php echo $prop['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this property?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-folder-open"></i>
                    <p>No properties listed by this user</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- User's Shortlisted Properties -->
        <div class="recent-section">
            <div class="section-header">
                <h2><i class="fas fa-heart"></i> Properties Shortlisted by <?php echo $user['email']; ?></h2>
            </div>
            
            <?php if($shortlistCount > 0): ?>
                <div class="property-grid">
                    <?php while($prop = mysqli_fetch_assoc($shortlistQuery)): ?>
                        <div class="property-card">
                            <img src="uploads/<?php echo $prop['image']; ?>" class="property-thumb" alt="<?php echo $prop['location']; ?>">
                            <div class="property-info">
                                <h4><?php echo $prop['location']; ?></h4>
                                <p class="property-price">₹<?php echo indianCurrency($prop['price']); ?></p>
                                <p class="property-meta"><?php echo $prop['rooms']; ?> • <?php echo $prop['property_type']; ?></p>
                                <div class="property-actions">
                                    <a href="admin_property_view.php?id=<?php echo $prop['id']; ?>" class="action-btn view-btn">View</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-heart-broken"></i>
                    <p>No properties shortlisted by this user</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>