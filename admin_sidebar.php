<div class="admin-sidebar">
    <div class="sidebar-header">
        <i class="fas fa-home"></i>
        <h3>NestFinder Admin</h3>
    </div>
    
    <div class="sidebar-user">
        <i class="fas fa-user-circle"></i>
        <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <small><i class="fas fa-envelope"></i> <?php echo $_SESSION['email']; ?></small>
    </div>
    
    <ul class="sidebar-nav">
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <a href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' || basename($_SERVER['PHP_SELF']) == 'admin_user_view.php' ? 'active' : ''; ?>">
            <a href="admin_users.php">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_properties.php' || basename($_SERVER['PHP_SELF']) == 'admin_property_view.php' ? 'active' : ''; ?>">
            <a href="admin_properties.php">
                <i class="fas fa-building"></i> Properties
            </a>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_shortlists.php' ? 'active' : ''; ?>">
            <a href="admin_shortlists.php">
                <i class="fas fa-heart"></i> Shortlists
            </a>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_reports.php' ? 'active' : ''; ?>">
            <a href="admin_reports.php">
                <i class="fas fa-chart-line"></i> Reports
            </a>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_user_reports.php' ? 'active' : ''; ?>">
            <a href="admin_user_reports.php">
                <i class="fas fa-flag"></i> User Issues
            </a>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_settings.php' ? 'active' : ''; ?>">
            <a href="admin_settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_edit_profile.php' ? 'active' : ''; ?>">
            <a href="admin_edit_profile.php">
                <i class="fas fa-user-circle"></i> My Profile
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>