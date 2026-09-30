<?php
session_start();
include("db.php");
include("admin_sidebar.php");

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];

$totalProperties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties"))['count'];
$totalShortlists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM shortlist"))['count'];
$furnishStats = mysqli_query($conn, "SELECT furnish, COUNT(*) as count FROM properties GROUP BY furnish");
$rentCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE type = 'Rent'"))['count'];
$saleCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE type = 'Sale'"))['count'];
$propertyTypes = mysqli_query($conn, "SELECT property_type, COUNT(*) as count FROM properties GROUP BY property_type");

$topShortlisted = mysqli_query($conn,
    "SELECT p.id, p.location, p.price, p.image, COUNT(s.id) as shortlist_count 
     FROM properties p 
     LEFT JOIN shortlist s ON p.id = s.property_id 
     GROUP BY p.id 
     ORDER BY shortlist_count DESC 
     LIMIT 10"
);


$topUsers = mysqli_query($conn,
    "SELECT u.user_id, u.email, COUNT(p.id) as property_count 
     FROM users u 
     LEFT JOIN properties p ON u.user_id = p.user_id 
     GROUP BY u.user_id 
     ORDER BY property_count DESC 
     LIMIT 10"
);

$avgSqft = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(sqft) as avg FROM properties WHERE sqft IS NOT NULL AND sqft > 0"))['avg'];


$pendingCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE status = 'pending'"))['count'];
$approvedCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE status = 'approved'"))['count'];
$rejectedCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE status = 'rejected'"))['count'];


$topLocations = mysqli_query($conn,
    "SELECT location, COUNT(*) as count 
     FROM properties 
     GROUP BY location 
     ORDER BY count DESC 
     LIMIT 10"
);

$newUsersQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$newUsers = mysqli_fetch_assoc($newUsersQuery)['count'];

$newPropertiesQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$newProperties = mysqli_fetch_assoc($newPropertiesQuery)['count'];


$newShortlistsQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM shortlist WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$newShortlists = mysqli_fetch_assoc($newShortlistsQuery)['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports | Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    
    <?php include("admin_sidebar.php"); ?>
    
    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-header">
            <h1>Reports & Analytics</h1>
            <p>View insights and statistics about your platform</p>
        </div>
        
        <!-- Date Range Filter -->
        <div class="date-filter">
            <form method="GET" action="" class="date-form">
                <div class="date-group">
                    <label><i class="fas fa-calendar-alt"></i> From:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="date-input">
                </div>
                <div class="date-group">
                    <label>To:</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="date-input">
                </div>
                <button type="submit" class="apply-date-btn">Apply Date Range</button>
            </form>
        </div>
        
        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Total Users</p>
                    <small>+<?php echo $newUsers; ?> new (30 days)</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalProperties; ?></h3>
                    <p>Total Properties</p>
                    <small>+<?php echo $newProperties; ?> new (30 days)</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalShortlists; ?></h3>
                    <p>Total Shortlists</p>
                    <small>+<?php echo $newShortlists; ?> new (30 days)</small>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="charts-row">
            <!-- Property Type Distribution -->
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Property Type Distribution</h3>
                <div class="chart-content">
                    <div class="pie-chart">
                        <div class="pie-segment rent" style="width: <?php echo $totalProperties > 0 ? ($rentCount / $totalProperties) * 100 : 0; ?>%">
                            <span>Rent</span>
                        </div>
                        <div class="pie-segment sale" style="width: <?php echo $totalProperties > 0 ? ($saleCount / $totalProperties) * 100 : 0; ?>%">
                            <span>Sale</span>
                        </div>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-color rent-color"></span>
                            <span>Rent: <?php echo $rentCount; ?> (<?php echo $totalProperties > 0 ? round(($rentCount / $totalProperties) * 100) : 0; ?>%)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color sale-color"></span>
                            <span>Sale: <?php echo $saleCount; ?> (<?php echo $totalProperties > 0 ? round(($saleCount / $totalProperties) * 100) : 0; ?>%)</span>
                        </div>
                    </div>
                </div>
            </div>
            
            
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Property Status</h3>
                <div class="chart-content">
                    <div class="status-stats">
                        <div class="status-stat pending">
                            <span class="status-value"><?php echo $pendingCount; ?></span>
                            <span class="status-name">Pending</span>
                        </div>
                        <div class="status-stat approved">
                            <span class="status-value"><?php echo $approvedCount; ?></span>
                            <span class="status-name">Approved</span>
                        </div>
                        <div class="status-stat rejected">
                            <span class="status-value"><?php echo $rejectedCount; ?></span>
                            <span class="status-name">Rejected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Property Type Details -->
        <div class="report-section">
            <h3><i class="fas fa-building"></i> Properties by Category</h3>
            <div class="category-grid">
                <?php while($type = mysqli_fetch_assoc($propertyTypes)): ?>
                    <div class="category-card">
                        <i class="fas fa-home"></i>
                        <div class="category-info">
                            <span class="category-name"><?php echo $type['property_type']; ?></span>
                            <span class="category-count"><?php echo $type['count']; ?> properties</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Top Locations -->
        <div class="report-section">
            <h3><i class="fas fa-map-marker-alt"></i> Top Locations</h3>
            <div class="location-list">
                <?php while($loc = mysqli_fetch_assoc($topLocations)): ?>
                    <div class="location-item">
                        <div class="location-name">
                            <i class="fas fa-map-pin"></i> <?php echo $loc['location']; ?>
                        </div>
                        <div class="location-count"><?php echo $loc['count']; ?> properties</div>
                        <div class="location-bar">
                            <div class="bar-fill" style="width: <?php echo ($loc['count'] / $totalProperties) * 100; ?>%"></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Most Shortlisted Properties -->
        <div class="report-section">
            <h3><i class="fas fa-heart"></i> Most Shortlisted Properties (Top 10)</h3>
            <div class="property-list">
                <?php while($prop = mysqli_fetch_assoc($topShortlisted)): ?>
                    <div class="property-item">
                        <img src="uploads/<?php echo $prop['image']; ?>" class="property-thumb-small" alt="<?php echo $prop['location']; ?>">
                        <div class="property-details">
                            <div class="property-name"><?php echo $prop['location']; ?></div>
                            <div class="property-price">₹<?php echo indianCurrency($prop['price']); ?></div>
                        </div>
                        <div class="property-shortlist-count">
                            <i class="fas fa-heart"></i> <?php echo $prop['shortlist_count']; ?> shortlists
                        </div>
                        <a href="admin_property_view.php?id=<?php echo $prop['id']; ?>" class="view-link">View →</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Most Active Users -->
        <div class="report-section">
            <h3><i class="fas fa-trophy"></i> Most Active Users (Most Listings)</h3>
            <div class="user-list">
                <?php while($user = mysqli_fetch_assoc($topUsers)): ?>
                    <div class="user-item">
                        <div class="user-avatar-small">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-details">
                            <div class="user-email"><?php echo $user['email']; ?></div>
                            <div class="user-stats"><?php echo $user['property_count']; ?> properties listed</div>
                        </div>
                        <a href="admin_user_view.php?id=<?php echo $user['user_id']; ?>" class="view-link">View →</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="quick-stat-card">
                <i class="fas fa-chart-line"></i>
                <div class="quick-stat-info">
                    <h4>Avg Properties per User</h4>
                    <p><?php echo $totalUsers > 0 ? round($totalProperties / $totalUsers, 1) : 0; ?></p>
                </div>
            </div>
            <div class="quick-stat-card">
                <i class="fas fa-arrows-alt"></i>
                <div class="quick-stat-info">
                    <h4>Avg Property Area</h4>
                    <p><?php echo $avgSqft ? round($avgSqft) . ' sq ft' : 'N/A'; ?></p>
                </div>
            </div>
            
            <div class="quick-stat-card">
                <i class="fas fa-chart-line"></i>
                <div class="quick-stat-info">
                    <h4>Avg Shortlists per Property</h4>
                    <p><?php echo $totalProperties > 0 ? round($totalShortlists / $totalProperties, 1) : 0; ?></p>
                </div>
            </div>
            <div class="quick-stat-card">
                <i class="fas fa-chart-line"></i>
                <div class="quick-stat-info">
                    <h4>Conversion Rate (Rent/Sale)</h4>
                    <p><?php echo $totalProperties > 0 ? round(($rentCount / $totalProperties) * 100) : 0; ?>% / <?php echo $totalProperties > 0 ? round(($saleCount / $totalProperties) * 100) : 0; ?>%</p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>