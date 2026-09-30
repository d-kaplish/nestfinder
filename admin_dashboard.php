<?php
session_start();
include("db.php");
include("admin_sidebar.php");

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$totalProperties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties"))['count'];
$totalShortlists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM shortlist"))['count'];

$pendingProperties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE status='pending'"))['count'];
$approvedProperties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE status='approved'"))['count'];
$rejectedProperties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE status='rejected'"))['count'];

$recentProperties = mysqli_query($conn, "SELECT p.*, u.email, u.name FROM properties p JOIN users u ON p.user_id = u.user_id ORDER BY p.id DESC LIMIT 5");

$recentUsers = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard | NestFinder</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    
    <?php include("admin_sidebar.php"); ?>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            <p><i class="fas fa-user-shield"></i> Welcome back, Admin <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p><i class="fas fa-user"></i> Total Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalProperties; ?></h3>
                    <p><i class="fas fa-home"></i> Total Properties</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalShortlists; ?></h3>
                    <p><i class="fas fa-heart"></i> Total Shortlists</p>
                </div>
            </div>
        </div>
        
        <div class="status-grid">
            <div class="status-card pending">
                <span class="status-label"><i class="fas fa-clock"></i> Pending</span>
                <span class="status-count"><?php echo $pendingProperties; ?></span>
            </div>
            <div class="status-card approved">
                <span class="status-label"><i class="fas fa-check-circle"></i> Approved</span>
                <span class="status-count"><?php echo $approvedProperties; ?></span>
            </div>
            <div class="status-card rejected">
                <span class="status-label"><i class="fas fa-times-circle"></i> Rejected</span>
                <span class="status-count"><?php echo $rejectedProperties; ?></span>
            </div>
        </div>
        
        <div class="recent-section">
            <div class="section-header">
                <h2><i class="fas fa-building"></i> Recent Properties</h2>
                <a href="admin_properties.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($prop = mysqli_fetch_assoc($recentProperties)): ?>
                    <tr>
                        <td><?php echo $prop['id']; ?></td>
                        <td><?php echo $prop['location']; ?></td>
                        <td><i class="fas fa-rupee-sign"></i> <?php echo indianCurrency($prop['price']); ?></td>
                        <td><?php echo $prop['name'] ?: $prop['email']; ?></td>
                        <td><span class="status-badge <?php echo $prop['status']; ?>"><?php echo $prop['status']; ?></span></td>
                        <td>
                            <a href="admin_property_view.php?id=<?php echo $prop['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="recent-section">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> Recent Users</h2>
                <a href="admin_users.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Joined</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($recentUsers)): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['contact']; ?></td>
                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td><?php echo $user['is_admin'] == 1 ? '<i class="fas fa-user-shield"></i> Admin' : '<i class="fas fa-user"></i> User'; ?></td>
                        <td>
                            <a href="admin_user_view.php?id=<?php echo $user['user_id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>