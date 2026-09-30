<?php
session_start();
include("db.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Filter variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : '';

// Build WHERE clause
$where = "WHERE 1=1";
if (!empty($search)) {
    $where .= " AND (p.location LIKE '%$search%' OR u.email LIKE '%$search%')";
}
if (!empty($user_filter)) {
    $where .= " AND s.user_id = $user_filter";
}

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count
$totalQuery = mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM shortlist s 
     JOIN properties p ON s.property_id = p.id 
     JOIN users u ON s.user_id = u.user_id 
     $where"
);
$totalShortlists = mysqli_fetch_assoc($totalQuery)['count'];
$totalPages = ceil($totalShortlists / $limit);

// Get shortlisted properties
$shortlistQuery = mysqli_query($conn,
    "SELECT s.*, p.location, p.price, p.image, p.type, p.property_type, p.rooms, u.email as user_email, u.contact as user_contact
     FROM shortlist s 
     JOIN properties p ON s.property_id = p.id 
     JOIN users u ON s.user_id = u.user_id 
     $where 
     ORDER BY s.id DESC 
     LIMIT $offset, $limit"
);

// Get all users for filter dropdown
$usersQuery = mysqli_query($conn, "SELECT user_id, email FROM users ORDER BY email");

// Remove single shortlist entry
if (isset($_GET['remove_id'])) {
    $removeId = intval($_GET['remove_id']);
    mysqli_query($conn, "DELETE FROM shortlist WHERE id = $removeId");
    header("Location: admin_shortlists.php?search=" . urlencode($search) . "&user_id=$user_filter&page=$page");
    exit();
}

// Bulk remove
if (isset($_POST['bulk_remove']) && isset($_POST['selected_ids'])) {
    $selectedIds = $_POST['selected_ids'];
    $ids = implode(",", array_map('intval', $selectedIds));
    mysqli_query($conn, "DELETE FROM shortlist WHERE id IN ($ids)");
    header("Location: admin_shortlists.php?search=" . urlencode($search) . "&user_id=$user_filter");
    exit();
}

// Export to CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="shortlist_export.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'User Email', 'Property Location', 'Property Price', 'Property Type', 'Shortlisted Date']);
    
    $exportQuery = mysqli_query($conn,
        "SELECT s.id, u.email, p.location, p.price, p.type, s.created_at
         FROM shortlist s 
         JOIN properties p ON s.property_id = p.id 
         JOIN users u ON s.user_id = u.user_id 
         ORDER BY s.id DESC"
    );
    
    while ($row = mysqli_fetch_assoc($exportQuery)) {
        $date = isset($row['created_at']) && !empty($row['created_at']) && $row['created_at'] != '0000-00-00 00:00:00' 
                ? date('d M Y', strtotime($row['created_at'])) 
                : 'N/A';
        fputcsv($output, [
            $row['id'],
            $row['email'],
            $row['location'],
            $row['price'],
            $row['type'],
            $date
        ]);
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Shortlists | Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    
    <?php include("admin_sidebar.php"); ?>
    
    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <h1>Manage Shortlists</h1>
            <p>View all shortlisted properties across all users</p>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search by location or user email..." value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
                </div>
                
                <div class="filter-group">
                    <select name="user_id" class="filter-select">
                        <option value="">All Users</option>
                        <?php while($user = mysqli_fetch_assoc($usersQuery)): ?>
                            <option value="<?php echo $user['user_id']; ?>" <?php echo $user_filter == $user['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['email']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="filter-btn">Apply Filters</button>
                    <a href="admin_shortlists.php" class="clear-filter-btn">Clear</a>
                    <a href="?export=1" class="export-btn"><i class="fas fa-download"></i> Export CSV</a>
                </div>
            </form>
        </div>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <form method="POST" action="" id="bulkForm">
                <div class="bulk-controls">
                    <select name="bulk_remove" class="bulk-select">
                        <option value="">Bulk Actions</option>
                        <option value="remove">Remove Selected</option>
                    </select>
                    <button type="submit" class="bulk-apply-btn" onclick="return confirm('Remove selected properties from shortlists?')">Apply</button>
                </div>
                
                <!-- Shortlists Table -->
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="30"><input type="checkbox" id="selectAll"></th>
                                <th width="50">ID</th>
                                <th width="80">Image</th>
                                <th>Property Location</th>
                                <th width="100">Price</th>
                                <th width="80">Area</th> 
                                <th width="80">Type</th>
                                <th>User Email</th>
                                <th width="100">User Contact</th>
                                <th width="150">Shortlisted Date</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($shortlistQuery) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($shortlistQuery)): 
                                    // Handle date safely - check if created_at exists and is valid
                                    $shortlist_date = 'N/A';
                                    if (isset($row['created_at']) && !empty($row['created_at']) && $row['created_at'] != '0000-00-00 00:00:00') {
                                        $shortlist_date = date('d M Y, h:i A', strtotime($row['created_at']));
                                    }
                                ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_ids[]" value="<?php echo $row['id']; ?>" class="shortlist-checkbox"></td>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><img src="uploads/<?php echo $row['image']; ?>" class="table-thumb" alt="Property Image" onerror="this.src='https://via.placeholder.com/50?text=No+Image'"></td>
                                    <td>
                                        <a href="admin_property_view.php?id=<?php echo $row['property_id']; ?>" class="property-link">
                                            <?php echo htmlspecialchars($row['location']); ?>
                                        </a>
                                    </td>
                                    <td>₹<?php echo indianCurrency($row['price']); ?></td>
                                    <td><?php echo isset($row['sqft']) && $row['sqft'] ? $row['sqft'] . ' sq ft' : 'N/A'; ?></td> 
                                    <td><span class="type-badge <?php echo strtolower($row['type']); ?>"><?php echo $row['type']; ?></span></td>
                                    <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_contact']); ?></td>
                                    <td><?php echo $shortlist_date; ?></td>
                                    <td class="action-buttons">
                                        <a href="admin_property_view.php?id=<?php echo $row['property_id']; ?>" class="action-btn view-btn" title="View Property">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?remove_id=<?php echo $row['id']; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&page=<?php echo $page; ?>" class="action-btn delete-btn" title="Remove from Shortlist" onclick="return confirm('Remove this property from shortlist?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="no-data">
                                        <i class="fas fa-heart-broken"></i>
                                        <p>No shortlisted properties found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <!-- Stats Summary -->
        <div class="stats-summary">
            <div class="summary-item">
                <i class="fas fa-heart"></i>
                <span>Total Shortlisted Entries: <?php echo $totalShortlists; ?></span>
            </div>
            <div class="summary-item">
                <i class="fas fa-users"></i>
                <span>Users with Shortlists: <?php 
                    $userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as count FROM shortlist"));
                    echo $userCount['count']; 
                ?></span>
            </div>
            <div class="summary-item">
                <i class="fas fa-building"></i>
                <span>Unique Properties Shortlisted: <?php 
                    $propCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT property_id) as count FROM shortlist"));
                    echo $propCount['count']; 
                ?></span>
            </div>
        </div>
    </div>
</div>

<script>
// Select All checkbox functionality
document.getElementById('selectAll').addEventListener('change', function() {
    let checkboxes = document.querySelectorAll('.shortlist-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>

</body>
</html>