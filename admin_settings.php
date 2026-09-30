<?php
session_start();
include("db.php");
include("admin_sidebar.php");

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

if (isset($_POST['update_settings'])) {
    $site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
    $site_email = mysqli_real_escape_string($conn, $_POST['site_email']);
    $site_phone = mysqli_real_escape_string($conn, $_POST['site_phone']);
    $contact_address = mysqli_real_escape_string($conn, $_POST['contact_address']);
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    $property_approval = isset($_POST['property_approval']) ? 1 : 0;
    
    mysqli_query($conn, "INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES ('site_name', '$site_name', 'text') 
                        ON DUPLICATE KEY UPDATE setting_value = '$site_name'");
    mysqli_query($conn, "INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES ('site_email', '$site_email', 'text') 
                        ON DUPLICATE KEY UPDATE setting_value = '$site_email'");
    mysqli_query($conn, "INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES ('site_phone', '$site_phone', 'text') 
                        ON DUPLICATE KEY UPDATE setting_value = '$site_phone'");
    mysqli_query($conn, "INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES ('contact_address', '$contact_address', 'text') 
                        ON DUPLICATE KEY UPDATE setting_value = '$contact_address'");
    mysqli_query($conn, "INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES ('maintenance_mode', '$maintenance_mode', 'boolean') 
                        ON DUPLICATE KEY UPDATE setting_value = '$maintenance_mode'");
    mysqli_query($conn, "INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES ('property_approval_required', '$property_approval', 'boolean') 
                        ON DUPLICATE KEY UPDATE setting_value = '$property_approval'");
    
    $message = "Settings updated successfully!";
}


$settings = [];
$settingsQuery = mysqli_query($conn, "SELECT setting_key, setting_value FROM site_settings");
while ($row = mysqli_fetch_assoc($settingsQuery)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}


$site_name = isset($settings['site_name']) ? $settings['site_name'] : 'NestFinder';
$site_email = isset($settings['site_email']) ? $settings['site_email'] : 'contact@nestfinder.com';
$site_phone = isset($settings['site_phone']) ? $settings['site_phone'] : '+91 9876543210';
$contact_address = isset($settings['contact_address']) ? $settings['contact_address'] : 'Mumbai, India';
$maintenance_mode = isset($settings['maintenance_mode']) ? $settings['maintenance_mode'] : 0;
$property_approval = isset($settings['property_approval_required']) ? $settings['property_approval_required'] : 0;


$logsPerPage = 20;
$logPage = isset($_GET['log_page']) ? (int)$_GET['log_page'] : 1;
$logOffset = ($logPage - 1) * $logsPerPage;

$totalLogsQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM activity_log");
$totalLogs = mysqli_fetch_assoc($totalLogsQuery)['count'];
$totalLogPages = ceil($totalLogs / $logsPerPage);

$logsQuery = mysqli_query($conn,
    "SELECT l.*, u.email as admin_email 
     FROM activity_log l 
     JOIN users u ON l.admin_id = u.user_id 
     ORDER BY l.created_at DESC 
     LIMIT $logOffset, $logsPerPage"
);

if (isset($_GET['clear_logs'])) {
    mysqli_query($conn, "TRUNCATE TABLE activity_log");
    header("Location: admin_settings.php");
    exit();
}

if (isset($_POST['test_email'])) {
    $message = "Test email functionality would be implemented here with PHPMailer";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Settings | Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">
    
    <?php include("admin_sidebar.php"); ?>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1><i class="fas fa-cog"></i> Admin Settings</h1>
            <p>Configure site settings and preferences</p>
        </div>
        
        <?php if($message): ?>
            <div class="success-message"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="settings-tabs">
            <button class="tab-btn active" onclick="showTab('general')"><i class="fas fa-globe"></i> General Settings</button>
            <button class="tab-btn" onclick="showTab('logs')"><i class="fas fa-history"></i> Activity Logs</button>
            <button class="tab-btn" onclick="showTab('database')"><i class="fas fa-database"></i> Database Info</button>
        </div>
        
        <div id="general-tab" class="tab-content active">
            <div class="settings-section">
                <h3><i class="fas fa-globe"></i> Site Settings</h3>
                <form method="POST" action="" class="settings-form">
                    <div class="form-group">
                        <label><i class="fas fa-home"></i> Site Name</label>
                        <input type="text" name="site_name" value="<?php echo $site_name; ?>" class="form-input">
                        <small><i class="fas fa-info-circle"></i> This appears in browser tabs and headers</small>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Site Email</label>
                        <input type="email" name="site_email" value="<?php echo $site_email; ?>" class="form-input">
                        <small><i class="fas fa-info-circle"></i> All system notifications will be sent from this email</small>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Site Phone</label>
                        <input type="text" name="site_phone" value="<?php echo $site_phone; ?>" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Contact Address</label>
                        <textarea name="contact_address" rows="2" class="form-textarea"><?php echo $contact_address; ?></textarea>
                    </div>
                    
                    <div class="setting-card">
                        <div class="setting-header">
                            <i class="fas fa-wrench"></i>
                            <div class="setting-info">
                                <h4>Maintenance Mode</h4>
                                <p>When enabled, only admins can access the site</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="maintenance_mode" <?php echo $maintenance_mode ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="setting-card">
                        <div class="setting-header">
                            <i class="fas fa-check-circle"></i>
                            <div class="setting-info">
                                <h4>Admin Approval for New Properties</h4>
                                <p>Properties will be hidden until approved by admin</p>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="property_approval" <?php echo $property_approval ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_settings" class="save-btn">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="settings-section">
                <h3><i class="fas fa-envelope"></i> Email Test</h3>
                <form method="POST" action="" class="settings-form">
                    <p><i class="fas fa-info-circle"></i> Test if your email configuration is working properly.</p>
                    <button type="submit" name="test_email" class="test-email-btn">
                        <i class="fas fa-paper-plane"></i> Send Test Email
                    </button>
                </form>
            </div>
        </div>
        
        <div id="logs-tab" class="tab-content">
            <div class="settings-section">
                <div class="section-header">
                    <h3><i class="fas fa-history"></i> Admin Activity Logs</h3>
                    <a href="?clear_logs=1" class="clear-logs-btn" onclick="return confirm('Clear all activity logs?')">
                        <i class="fas fa-trash"></i> Clear All Logs
                    </a>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Target Type</th>
                                <th>Target ID</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($logsQuery) > 0): ?>
                                <?php while($log = mysqli_fetch_assoc($logsQuery)): ?>
                                <tr>
                                    <td><?php echo $log['log_id']; ?></td>
                                    <td><?php echo $log['admin_email']; ?></td>
                                    <td>
                                        <span class="action-badge <?php echo $log['action']; ?>">
                                            <?php echo $log['action']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $log['target_type'] ?: '-'; ?></td>
                                    <td><?php echo $log['target_id'] ?: '-'; ?></td>
                                    <td><?php echo $log['details'] ?: '-'; ?></td>
                                    <td><?php echo $log['ip_address'] ?: '-'; ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($log['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-data"><i class="fas fa-folder-open"></i> No activity logs found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for logs -->
                <?php if($totalLogPages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $totalLogPages; $i++): ?>
                        <a href="?log_page=<?php echo $i; ?>" class="page-link <?php echo $i == $logPage ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Database Info Tab -->
        <div id="database-tab" class="tab-content">
            <div class="settings-section">
                <h3><i class="fas fa-database"></i> Database Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-database"></i> Database Name:</span>
                        <span class="info-value"><?php echo $database; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-server"></i> Host:</span>
                        <span class="info-value"><?php echo $host; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-table"></i> Total Tables:</span>
                        <span class="info-value">
                            <?php 
                            $tables = mysqli_query($conn, "SHOW TABLES");
                            echo mysqli_num_rows($tables);
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="settings-section">
                <h3><i class="fas fa-chart-bar"></i> Table Statistics</h3>
                <div class="table-stats">
                    <?php
                    $tables = ['users', 'properties', 'pictures', 'shortlist', 'activity_log', 'site_settings'];
                    foreach($tables as $table):
                        $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM $table"))['count'];
                    ?>
                    <div class="stat-row">
                        <span class="stat-table"><?php echo $table; ?></span>
                        <span class="stat-count"><?php echo $count; ?> records</span>
                        <div class="stat-bar">
                            <div class="stat-fill" style="width: <?php echo min(100, $count / 10); ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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