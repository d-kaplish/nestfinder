<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "nestfinder";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

function indianCurrency($number) {
    $number = (int)$number;
    if ($number < 1000) {
        return "₹ " . $number;
    }
    
    $number = (string)$number;
    $len = strlen($number);
    
    if ($len == 4) {
        return "₹ " . substr($number, 0, 1) . "," . substr($number, 1);
    }
    if ($len == 5) {
        return "₹ " . substr($number, 0, 2) . "," . substr($number, 2);
    }
    if ($len == 6) {
        return "₹ " . substr($number, 0, 3) . "," . substr($number, 3);
    }
    if ($len == 7) {
        return "₹ " . substr($number, 0, 1) . "," . substr($number, 1, 2) . "," . substr($number, 3);
    }
    if ($len == 8) {
        return "₹ " . substr($number, 0, 2) . "," . substr($number, 2, 2) . "," . substr($number, 4);
    }
    if ($len == 9) {
        return "₹ " . substr($number, 0, 3) . "," . substr($number, 3, 2) . "," . substr($number, 5);
    }
    
    return "₹ " . number_format($number);
}
?>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">