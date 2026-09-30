<?php
session_start();
include "db.php";
include "admin_sidebar.php";

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

$where = "WHERE 1=1";
if (!empty($search)) {
    $where .= " AND (p.location LIKE '%$search%' OR u.email LIKE '%$search%' OR u.name LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $where .= " AND p.status = '$status_filter'";
}
if (!empty($type_filter)) {
    $where .= " AND p.type = '$type_filter'";
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM properties p JOIN users u ON p.user_id = u.user_id $where");
$totalProperties = mysqli_fetch_assoc($totalQuery)['count'];
$totalPages = ceil($totalProperties / $limit);

$propertiesQuery = mysqli_query($conn, 
    "SELECT p.*, u.email, u.name 
     FROM properties p 
     JOIN users u ON p.user_id = u.user_id 
     $where 
     ORDER BY p.id DESC 
     LIMIT $offset, $limit"
);

if (isset($_POST['bulk_action']) && isset($_POST['selected_ids'])) {
    $selectedIds = $_POST['selected_ids'];
    $ids = implode(",", array_map('intval', $selectedIds));
    $action = $_POST['bulk_action'];
    
    if ($action == 'approve') {
        mysqli_query($conn, "UPDATE properties SET status = 'approved' WHERE id IN ($ids)");
    } elseif ($action == 'reject') {
        mysqli_query($conn, "UPDATE properties SET status = 'rejected' WHERE id IN ($ids)");
    } elseif ($action == 'delete') {
        $props = mysqli_query($conn, "SELECT id, image FROM properties WHERE id IN ($ids)");
        while ($prop = mysqli_fetch_assoc($props)) {
            $pics = mysqli_query($conn, "SELECT file_name FROM pictures WHERE property_id='{$prop['id']}'");
            while ($pic = mysqli_fetch_assoc($pics)) {
                @unlink("uploads/".$pic['file_name']);
            }
            mysqli_query($conn, "DELETE FROM pictures WHERE property_id='{$prop['id']}'");
            @unlink("uploads/".$prop['image']);
        }
        mysqli_query($conn, "DELETE FROM properties WHERE id IN ($ids)");
        mysqli_query($conn, "DELETE FROM shortlist WHERE property_id IN ($ids)");
    }
    header("Location: admin_properties.php?status=$status_filter&type=$type_filter&search=" . urlencode($search));
    exit();
}

if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    
    $pics = mysqli_query($conn, "SELECT file_name FROM pictures WHERE property_id='$deleteId'");
    while ($pic = mysqli_fetch_assoc($pics)) {
        @unlink("uploads/".$pic['file_name']);
    }
    mysqli_query($conn, "DELETE FROM pictures WHERE property_id='$deleteId'");
    
    $prop = mysqli_query($conn, "SELECT image FROM properties WHERE id='$deleteId'");
    if ($p = mysqli_fetch_assoc($prop)) {
        @unlink("uploads/".$p['image']);
    }
    
    mysqli_query($conn, "DELETE FROM properties WHERE id='$deleteId'");
    mysqli_query($conn, "DELETE FROM shortlist WHERE property_id='$deleteId'");
    
    header("Location: admin_properties.php?status=$status_filter&type=$type_filter&search=" . urlencode($search));
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Properties | Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    
    <?php include("admin_sidebar.php"); ?>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1><i class="fas fa-building"></i> Manage Properties</h1>
            <p>View, filter, approve, and manage all property listings</p>
        </div>
        
        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search by location or owner..." value="<?php echo htmlspecialchars($search); ?>" class="filter-input">
                </div>
                
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <option value="Rent" <?php echo $type_filter == 'Rent' ? 'selected' : ''; ?>>Rent</option>
                        <option value="Sale" <?php echo $type_filter == 'Sale' ? 'selected' : ''; ?>>Sale</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Apply</button>
                    <a href="admin_properties.php" class="clear-filter-btn"><i class="fas fa-times"></i> Clear</a>
                </div>
            </form>
        </div>
        
        <div class="bulk-actions">
            <form method="POST" action="" id="bulkForm">
                <div class="bulk-controls">
                    <select name="bulk_action" class="bulk-select">
                        <option value="">Bulk Actions</option>
                        <option value="approve">Approve Selected</option>
                        <option value="reject">Reject Selected</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" class="bulk-apply-btn" onclick="return confirm('Apply bulk action to selected properties?')">Apply</button>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="30"><input type="checkbox" id="selectAll"></th>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Area</th>
                                <th>Type</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($propertiesQuery) > 0): ?>
                                <?php while($prop = mysqli_fetch_assoc($propertiesQuery)): ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_ids[]" value="<?php echo $prop['id']; ?>" class="property-checkbox"></td>
                                    <td><?php echo $prop['id']; ?></td>
                                    <td><img src="uploads/<?php echo $prop['image']; ?>" class="table-thumb" alt="Property Image"></td>
                                    <td><?php echo htmlspecialchars($prop['location']); ?></td>
                                    <td><i class="fas fa-rupee-sign"></i> <?php echo indianCurrency($prop['price']); ?></td>
                                    <td><?php echo isset($prop['sqft']) && $prop['sqft'] ? $prop['sqft'] . ' sq ft' : 'N/A'; ?></td>
                                    <td><span class="type-badge <?php echo strtolower($prop['type']); ?>"><?php echo $prop['type']; ?></span></td>
                                    <td>
                                        <?php echo htmlspecialchars($prop['name']); ?><br>
                                        <small><?php echo $prop['email']; ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $prop['status']; ?>">
                                            <?php echo ucfirst($prop['status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="admin_property_view.php?id=<?php echo $prop['id']; ?>" class="action-btn view-btn" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?delete_id=<?php echo $prop['id']; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Delete this property?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="no-data">
                                        <i class="fas fa-inbox"></i>
                                        <p>No properties found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <div class="stats-summary">
            <div class="summary-item">
                <i class="fas fa-building"></i>
                <span>Total Properties: <?php echo $totalProperties; ?></span>
            </div>
            <div class="summary-item">
                <i class="fas fa-clock"></i>
                <span>Pending: <?php 
                    $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE status='pending'"));
                    echo $pending['count'];
                ?></span>
            </div>
            <div class="summary-item">
                <i class="fas fa-check-circle"></i>
                <span>Approved: <?php 
                    $approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE status='approved'"));
                    echo $approved['count'];
                ?></span>
            </div>
            <div class="summary-item">
                <i class="fas fa-times-circle"></i>
                <span>Rejected: <?php 
                    $rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE status='rejected'"));
                    echo $rejected['count'];
                ?></span>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function() {
    let checkboxes = document.querySelectorAll('.property-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>

</body>
</html>