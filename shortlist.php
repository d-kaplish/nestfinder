<?php
session_start();
include("db.php");
include("navbar.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn,
    "SELECT p.* FROM properties p
     JOIN shortlist s ON p.id = s.property_id
     WHERE s.user_id='$user_id'"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shortlist</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<h2>Your Shortlisted Properties <i class="fas fa-heart"></i></h2>

<div class="compare-container">
    <a href="compare_properties.php" class="compare-btn">
        <i class="fas fa-chart-line"></i> Compare Properties
    </a>
</div>

<div class="listing-container">

<?php if(mysqli_num_rows($query) > 0) { ?>

    <?php while($row = mysqli_fetch_assoc($query)) { ?>

        <div class="listing-card">
            <img src="uploads/<?php echo $row['image']; ?>">
            <div class="listing-info">
                <h3><?php echo $row['location']; ?></h3>
                ₹<?php echo $row['price']; ?><br>
                 Area: <?php echo isset($row['sqft']) && $row['sqft'] ? $row['sqft'] . ' sq ft' : 'N/A'; ?><br>
                <?php echo $row['rooms']; ?>

                <a href="remove_shortlist.php?id=<?php echo $row['id']; ?>" class="s-remove-btn">Remove</a>            
            </div>
        </div>

    <?php } ?>

<?php } else { ?>

    <h2><i class="fas fa-folder-open"></i><br>No properties in shortlist</h2>

<?php } ?>

</div>

</body>
</html>