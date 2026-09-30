<?php
session_start();
include("db.php");
include("navbar.php");


$where_conditions = ["p.status = 'approved'"]; 
$params = [];
$types = "";

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where_conditions[] = "p.location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}

if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "i";
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "i";
}

if (isset($_GET['property_type']) && !empty($_GET['property_type'])) {
    $types_array = explode(',', $_GET['property_type']);
    $placeholders = implode(',', array_fill(0, count($types_array), '?'));
    $where_conditions[] = "p.property_type IN ($placeholders)";
    foreach ($types_array as $t) {
        $params[] = $t;
        $types .= "s";
    }
}

if (isset($_GET['bhk']) && !empty($_GET['bhk'])) {
    $bhk_array = explode(',', $_GET['bhk']);
    $placeholders = implode(',', array_fill(0, count($bhk_array), '?'));
    $where_conditions[] = "p.rooms IN ($placeholders)";
    foreach ($bhk_array as $b) {
        $params[] = $b;
        $types .= "s";
    }
}

if (isset($_GET['tenant']) && !empty($_GET['tenant'])) {
    $tenant_array = explode(',', $_GET['tenant']);
    $placeholders = implode(',', array_fill(0, count($tenant_array), '?'));
    $where_conditions[] = "p.preferred_tenant IN ($placeholders)";
    foreach ($tenant_array as $t) {
        $params[] = $t;
        $types .= "s";
    }
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Sorting
$order_by = "p.id DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'lowest':
            $order_by = "p.price ASC";
            break;
        case 'highest':
            $order_by = "p.price DESC";
            break;
        case 'latest':
            $order_by = "p.id DESC";
            break;
    }
}

// Query with filters
$query = "SELECT p.* FROM properties p $where_clause ORDER BY $order_by";
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>NestFinder | Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="hero-section">
    <h1><i class="fas fa-home"></i> Welcome to NestFinder</h1>
    <p><i class="fas fa-search"></i> Find it. List it. Live it. <i class="fas fa-heart"></i></p>
    <?php if(isset($_SESSION['user_id'])): ?>
        <p><i class="fas fa-user"></i> Hello, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
    <?php endif; ?>
</div>

<hr>

<h2><i class="fas fa-building"></i> Latest Properties</h2>

<table cellpadding="10" width="100%">
<?php
if (mysqli_num_rows($result) > 0) {
    $count = 0;
    echo "<tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<td align='center'>";
        echo "<a href='display.php?id=" . $row['id'] . "' style='text-decoration:none; color:black;'>";
        echo "<img src='uploads/" . $row['image'] . "' width='200' height='150'><br><br>";
        echo "<strong><i class='fas fa-map-marker-alt'></i> Location:</strong> " . $row['location'] . "<br>";
        echo "<strong><i class='fas fa-rupee-sign'></i> Price: ₹" . $row['price'] . "</strong><br>";
        echo "<strong><i class='fas fa-arrows-alt'></i> Area:</strong> " . ($row['sqft'] ? $row['sqft'] . ' sq ft' : 'N/A') . "<br>";
        echo "<strong><i class='fas fa-tag'></i> Type:</strong> " . $row['type'];
        echo "</a>";
        echo "</td>";
        $count++;
        if ($count % 3 == 0) {
            echo "</tr><tr>";
        }
    }
    echo "</tr>";
} else {
    echo "<tr><td colspan='3' class='no-data'><i class='fas fa-folder-open'></i> No properties available</td></tr>";
}
?>
</table>

</body>
</html>