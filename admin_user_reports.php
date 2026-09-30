<?php
session_start();
include("db.php");
include("admin_sidebar.php");

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$sort_order = isset($_GET['sort']) && $_GET['sort'] == 'oldest' ? 'ASC' : 'DESC';

$where = "";
if ($status_filter != 'all') {
    $where = "WHERE r.status = '$status_filter'";
}

$reportsQuery = mysqli_query($conn, "
    SELECT r.*, p.location, p.image, p.price, p.type, u.name as user_name, u.email as user_email
    FROM reports r
    JOIN properties p ON r.property_id = p.id
    JOIN users u ON r.user_id = u.user_id
    $where
    ORDER BY r.created_at $sort_order
");

if (isset($_POST['update_status'])) {
    $report_id = intval($_POST['report_id']);
    $new_status = $_POST['status'];
    $update = mysqli_query($conn, "UPDATE reports SET status = '$new_status' WHERE report_id = '$report_id'");
    if ($update) {
        header("Location: admin_user_reports.php?status=$status_filter&sort=" . ($sort_order == 'DESC' ? 'newest' : 'oldest'));
        exit();
    }
}

if (isset($_POST['status']) && isset($_POST['report_id'])) {
    $report_id = intval($_POST['report_id']);
    $new_status = $_POST['status'];
    $update = mysqli_query($conn, "UPDATE reports SET status = '$new_status' WHERE report_id = '$report_id'");
    if ($update) {
        header("Location: admin_user_reports.php?status=$status_filter&sort=" . ($sort_order == 'DESC' ? 'newest' : 'oldest'));
        exit();
    }
}

$totalReports = mysqli_num_rows($reportsQuery);
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Reports | Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    
    <?php include("admin_sidebar.php"); ?>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1><i class="fas fa-flag"></i> User Reports</h1>
            <p>Manage and review user-reported property issues</p>
        </div>
        
        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <label><i class="fas fa-filter"></i> Status</label>
                    <select name="status" class="filter-select">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Reports</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="reviewed" <?php echo $status_filter == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                        <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-sort"></i> Sort By</label>
                    <select name="sort" class="filter-select">
                        <option value="newest" <?php echo $sort_order == 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort_order == 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Apply</button>
                    <a href="admin_user_reports.php" class="clear-filter-btn"><i class="fas fa-times"></i> Clear</a>
                </div>
            </form>
        </div>
        
        <div class="stats-summary">
            <div class="summary-item">
                <i class="fas fa-flag"></i>
                <span>Total Reports: <?php echo $totalReports; ?></span>
            </div>
            <div class="summary-item">
                <i class="fas fa-clock"></i>
                <span>Pending: <?php 
                    $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reports WHERE status='pending'"));
                    echo $pending['count'];
                ?></span>
            </div>
            <div class="summary-item">
                <i class="fas fa-check-circle"></i>
                <span>Resolved: <?php 
                    $resolved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reports WHERE status='resolved'"));
                    echo $resolved['count'];
                ?></span>
            </div>
        </div>
        
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Property</th>
                        <th>Reported By</th>
                        <th>Report Type</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Reported On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($reportsQuery) > 0): ?>
                        <?php while($report = mysqli_fetch_assoc($reportsQuery)): ?>
                            <tr>
                                <td><?php echo $report['report_id']; ?></td>
                                <td>
                                    <div class="property-info-cell">
                                        <img src="uploads/<?php echo $report['image']; ?>" class="table-thumb" alt="Property">
                                        <div>
                                            <strong><?php echo htmlspecialchars($report['location']); ?></strong><br>
                                            <small>₹<?php echo indianCurrency($report['price']); ?> • <?php echo $report['type']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($report['user_name']); ?></strong><br>
                                        <small><?php echo $report['user_email']; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $type_labels = [
                                        'fake_listing' => '<span class="report-type fake"><i class="fas fa-ban"></i> Fake Listing</span>',
                                        'wrong_price' => '<span class="report-type price"><i class="fas fa-rupee-sign"></i> Wrong Price</span>',
                                        'already_sold' => '<span class="report-type sold"><i class="fas fa-check-double"></i> Already Sold</span>',
                                        'inappropriate_content' => '<span class="report-type inappropriate"><i class="fas fa-exclamation-triangle"></i> Inappropriate</span>',
                                        'other' => '<span class="report-type other"><i class="fas fa-question"></i> Other</span>'
                                    ];
                                    echo isset($type_labels[$report['report_type']]) ? $type_labels[$report['report_type']] : $report['report_type'];
                                    ?>
                                </td>
                                <td>
                                    <div class="report-reason">
                                        <?php echo htmlspecialchars(substr($report['report_reason'], 0, 60)); ?>
                                        <?php echo strlen($report['report_reason']) > 60 ? '...' : ''; ?>
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" action="" class="status-update-form" style="display:inline;">
                                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                        <select name="status" class="status-select-small" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $report['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="reviewed" <?php echo $report['status'] == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                            <option value="resolved" <?php echo $report['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        </select>
                                        <input type="submit" name="update_status" value="Update" style="display:none;">
                                    </form>
                                </td>
                                <td><?php echo date('d M Y, h:i A', strtotime($report['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <a href="admin_property_view.php?id=<?php echo $report['property_id']; ?>" class="action-btn view-btn" title="View Property">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <p>No reports found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>