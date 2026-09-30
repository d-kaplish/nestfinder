<?php
session_start();
include("db.php");
include("navbar.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'updates';

// Get all properties owned by the user
$userPropertiesQuery = mysqli_query($conn, "SELECT id, location, image FROM properties WHERE user_id = '$user_id'");
$userPropertyIds = [];
while ($prop = mysqli_fetch_assoc($userPropertiesQuery)) {
    $userPropertyIds[] = $prop['id'];
}

// Get shortlist notifications
$shortlistNotifications = [];
if (!empty($userPropertyIds)) {
    $propertyIdsStr = implode(",", $userPropertyIds);
    $shortlistQuery = mysqli_query($conn, "
        SELECT s.*, p.location, p.image, p.id as property_id, 
               u.name as user_name, u.email as user_email, u.contact as user_contact
        FROM shortlist s
        JOIN properties p ON s.property_id = p.id
        JOIN users u ON s.user_id = u.user_id
        WHERE s.property_id IN ($propertyIdsStr)
        ORDER BY s.created_at DESC
    ");
    while ($row = mysqli_fetch_assoc($shortlistQuery)) {
        $shortlistNotifications[] = $row;
    }
}

// Get reports on user's properties
$reportNotifications = [];
if (!empty($userPropertyIds)) {
    $propertyIdsStr = implode(",", $userPropertyIds);
    $reportQuery = mysqli_query($conn, "
        SELECT r.*, p.location, p.image, p.id as property_id,
               u.name as reporter_name, u.email as reporter_email, u.contact as reporter_contact
        FROM reports r
        JOIN properties p ON r.property_id = p.id
        JOIN users u ON r.user_id = u.user_id
        WHERE r.property_id IN ($propertyIdsStr)
        ORDER BY r.created_at DESC
    ");
    while ($row = mysqli_fetch_assoc($reportQuery)) {
        $reportNotifications[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications | NestFinder</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="notifications-container">
    <div class="notifications-header">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <p>Stay updated about your property listings</p>
    </div>

    <div class="notifications-tabs">
        <a href="?tab=updates" class="tab-link <?php echo $active_tab == 'updates' ? 'active' : ''; ?>">
            <i class="fas fa-heart"></i> Updates
            <?php if(count($shortlistNotifications) > 0): ?>
                <span class="badge-count"><?php echo count($shortlistNotifications); ?></span>
            <?php endif; ?>
        </a>
        <a href="?tab=reports" class="tab-link <?php echo $active_tab == 'reports' ? 'active' : ''; ?>">
            <i class="fas fa-flag"></i> Reports
            <?php if(count($reportNotifications) > 0): ?>
                <span class="badge-count"><?php echo count($reportNotifications); ?></span>
            <?php endif; ?>
        </a>
    </div>

    <?php if($active_tab == 'updates'): ?>
    <div class="notifications-content">
        <div class="section-header">
            <h2><i class="fas fa-heart"></i> Shortlist Updates</h2>
            <p>People who have shortlisted your properties</p>
        </div>
        
        <?php if(count($shortlistNotifications) > 0): ?>
            <div class="notifications-list">
                <?php foreach($shortlistNotifications as $n): ?>
                <div class="notification-card">
                    <div class="notification-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="notification-details">
                        <div class="user-name"><?php echo htmlspecialchars($n['user_name']); ?></div>
                        <div class="user-contact">
                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($n['user_email']); ?></span>
                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($n['user_contact']); ?></span>
                        </div>
                        <div class="property-box">
                            <img src="uploads/<?php echo $n['image']; ?>" alt="Property">
                            <div>
                                <a href="display.php?id=<?php echo $n['property_id']; ?>"><?php echo htmlspecialchars($n['location']); ?></a>
                                <span class="property-date"><i class="fas fa-calendar-alt"></i> Shortlisted on: <?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-heart-broken"></i>
                <h3>No Shortlist Updates</h3>
                <p>When someone shortlists your property, you'll see it here.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if($active_tab == 'reports'): ?>
    <div class="notifications-content">
        <div class="section-header">
            <h2><i class="fas fa-flag"></i> Reports on Your Properties</h2>
            <p>User reports and concerns about your listings</p>
        </div>
        
        <?php if(count($reportNotifications) > 0): ?>
            <div class="notifications-list">
                <?php foreach($reportNotifications as $r): 
                    $typeLabels = [
                        'fake_listing' => 'Fake/Fraud Listing',
                        'wrong_price' => 'Wrong Price',
                        'already_sold' => 'Already Sold/Rented',
                        'inappropriate_content' => 'Inappropriate Content',
                        'other' => 'Other'
                    ];
                    $typeName = isset($typeLabels[$r['report_type']]) ? $typeLabels[$r['report_type']] : $r['report_type'];
                ?>
                <div class="notification-card">
                    <div class="notification-icon" style="background:#fff3e0;">
                        <i class="fas fa-flag" style="color:#ff9800;"></i>
                    </div>
                    <div class="notification-details">
                        <div class="user-name">Reported by: <?php echo htmlspecialchars($r['reporter_name']); ?></div>
                        <div class="user-contact">
                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($r['reporter_email']); ?></span>
                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($r['reporter_contact']); ?></span>
                        </div>
                        <div>
                            <span class="report-type <?php echo $r['report_type']; ?>"><?php echo $typeName; ?></span>
                            <span class="report-status <?php echo $r['status']; ?>">Status: <?php echo ucfirst($r['status']); ?></span>
                        </div>
                        <div class="report-reason">
                            <strong><i class="fas fa-comment"></i> Report Reason:</strong><br>
                            <?php echo nl2br(htmlspecialchars($r['report_reason'])); ?>
                        </div>
                        <div class="property-box">
                            <img src="uploads/<?php echo $r['image']; ?>" alt="Property">
                            <div>
                                <a href="display.php?id=<?php echo $r['property_id']; ?>"><?php echo htmlspecialchars($r['location']); ?></a>
                                <span class="property-date"><i class="fas fa-calendar-alt"></i> Reported on: <?php echo date('d M Y, h:i A', strtotime($r['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-flag-checkered"></i>
                <h3>No Reports Yet</h3>
                <p>When someone reports your property, you'll see it here.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>