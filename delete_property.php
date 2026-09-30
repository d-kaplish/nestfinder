<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Delete extra images first
    $pics = mysqli_query($conn, "SELECT file_name FROM pictures WHERE property_id='$id'");
    while ($pic = mysqli_fetch_assoc($pics)) {
        @unlink("uploads/".$pic['file_name']);
    }
    mysqli_query($conn, "DELETE FROM pictures WHERE property_id='$id'");

    // Delete main image
    $prop = mysqli_query($conn, "SELECT image FROM properties WHERE id='$id' AND user_id='$user_id'");
    if ($p = mysqli_fetch_assoc($prop)) {
        @unlink("uploads/".$p['image']);
    }

    // Delete property row
    mysqli_query($conn, "DELETE FROM properties WHERE id='$id' AND user_id='$user_id'");
}

header("Location: my_listings.php");
exit();
?>