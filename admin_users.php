<?php
session_start();
include("db.php");
include("admin_sidebar.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = "";
if (!empty($search)) {
    $where = "WHERE email LIKE '%$search%' OR contact LIKE '%$search%'";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM users $where");
$totalUsers = mysqli_fetch_assoc($totalQuery)['count'];
$totalPages = ceil($totalUsers / $limit);

// Get users
$usersQuery = mysqli_query($conn, "SELECT * FROM users $where ORDER BY user_id DESC LIMIT $offset, $limit");

// Delete user action
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    // Don't allow admin to delete themselves
    if ($deleteId != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $deleteId");
    }
    header("Location: admin_users.php");
    exit();
}

// Toggle admin status
if (isset($_GET['toggle_admin'])) {
    $userId = intval($_GET['toggle_admin']);
    // Don't allow changing own admin status
    if ($userId != $_SESSION['user_id']) {
        $current = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_admin FROM users WHERE user_id = $userId"));
        $newStatus = $current['is_admin'] == 1 ? 0 : 1;
        mysqli_query($conn, "UPDATE users SET is_admin = $newStatus WHERE user_id = $userId");
    }
    header("Location: admin_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users | Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    
    <?php include("admin_sidebar.php"); ?>
    
    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <h1>Manage Users</h1>
            <p>View, search, and manage all registered users</p>
        </div>
        
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search by email or contact..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">Search</button>
                    <?php if(!empty($search)): ?>
                        <a href="admin_users.php" class="clear-btn">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Joined Date</th>
                        <th>Properties</th>
                        <th>Shortlists</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($usersQuery) > 0): ?>
                        <?php while($user = mysqli_fetch_assoc($usersQuery)): 
                            $propCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE user_id = {$user['user_id']}"))['count'];
                            $shortCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM shortlist WHERE user_id = {$user['user_id']}"))['count'];
                        ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['contact']; ?></td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td><?php echo $propCount; ?></td>
                            <td><?php echo $shortCount; ?></td>
                            <td>
                                <span class="role-badge <?php echo $user['is_admin'] == 1 ? 'admin' : 'user'; ?>">
                                    <?php echo $user['is_admin'] == 1 ? 'Admin' : 'User'; ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="admin_user_view.php?id=<?php echo $user['user_id']; ?>" class="action-btn view-btn" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if($user['user_id'] != $_SESSION['user_id']): ?>
                                    <a href="?toggle_admin=<?php echo $user['user_id']; ?>" class="action-btn role-btn" title="Toggle Admin Role" onclick="return confirm('Change user role?')">
                                        <i class="fas fa-user-shield"></i>
                                    </a>
                                    <a href="?delete_id=<?php echo $user['user_id']; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Delete this user? All their properties will also be deleted.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <!-- Stats Summary -->
        <div class="stats-summary">
            <div class="summary-item">
                <i class="fas fa-users"></i>
                <span>Total Users: <?php echo $totalUsers; ?></span>
            </div>
            <div class="summary-item">
                <i class="fas fa-user-shield"></i>
                <span>Admins: <?php echo mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE is_admin = 1"))['count']; ?></span>
            </div>
        </div>
    </div>
</div>

</body>
</html>