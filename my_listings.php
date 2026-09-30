<?php
session_start();
include("db.php");
include("navbar.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "SELECT * FROM properties WHERE user_id='$user_id' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Listings</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<h2 style="text-align:center;"><i class="fas fa-building"></i> My Listings</h2>

<div class="listing-container">

<?php
if (mysqli_num_rows($query) == 0) {
    echo "<p style='text-align:center;'><i class='fas fa-folder-open'></i> No listings yet</p>";
}

while ($row = mysqli_fetch_assoc($query)) {
    $status_badge = '';
    $status_message = '';
    
    if ($row['status'] == 'pending') {
        $status_badge = '<div class="status-badge pending-badge"><i class="fas fa-clock"></i> Waiting for Admin Approval</div>';
        $status_message = '<small class="pending-message"><i class="fas fa-info-circle"></i> Your property is under review</small>';
    } elseif ($row['status'] == 'rejected') {
        $status_badge = '<div class="status-badge rejected-badge"><i class="fas fa-times-circle"></i> Rejected Post</div>';
        $status_message = '<small class="rejected-message"><i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($row['admin_notes'] ?: 'Your property was not approved. Please contact admin.') . '</small>';
    } else {
        $status_badge = '<div class="status-badge approved-badge"><i class="fas fa-check-circle"></i> Approved</div>';
    }
?>

<div class="listing-card">
    <?php echo $status_badge; ?>
    <img src="uploads/<?php echo $row['image']; ?>">
    <div class="listing-info">
        <h3><?php echo $row['location']; ?></h3>
        <p><i class="fas fa-rupee-sign"></i> <?php echo $row['price']; ?></p>
        <p><i class="fas fa-arrows-alt"></i> Area: <?php echo isset($row['sqft']) && $row['sqft'] ? $row['sqft'] . ' sq ft' : 'N/A'; ?></p> 
        <p><i class="fas fa-building"></i> <?php echo $row['property_type']; ?> • <i class="fas fa-bed"></i> <?php echo $row['rooms']; ?></p>
        <p><i class="fas fa-tag"></i> <?php echo $row['type']; ?></p>
        
        <?php echo $status_message; ?>

        <a href="edit_property.php?id=<?php echo $row['id']; ?>" class="edit-btn">
            <i class="fas fa-pen"></i> Edit
        </a>

        <a href="delete_property.php?id=<?php echo $row['id']; ?>" class="s-remove-btn" onclick="return confirm('Are you sure you want to delete this property?');">
            <i class="fas fa-trash"></i> Delete
        </a>
    </div>
</div>

<?php } ?>

</div>



</body>
</html>