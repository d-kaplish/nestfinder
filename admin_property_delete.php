<?php
session_start();
include("db.php");
include("admin_sidebar.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($property_id > 0) {
    // Get property main image
    $propQuery = mysqli_query($conn, "SELECT image FROM properties WHERE id = $property_id");
    $property = mysqli_fetch_assoc($propQuery);
    
    if ($property) {
        // Delete all extra images from pictures table
        $picsQuery = mysqli_query($conn, "SELECT file_name FROM pictures WHERE property_id = $property_id");
        while ($pic = mysqli_fetch_assoc($picsQuery)) {
            $file_path = "uploads/" . $pic['file_name'];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
        mysqli_query($conn, "DELETE FROM pictures WHERE property_id = $property_id");
        
        // Delete main image
        $main_image_path = "uploads/" . $property['image'];
        if (file_exists($main_image_path)) {
            @unlink($main_image_path);
        }
        
        // Delete property from shortlist
        mysqli_query($conn, "DELETE FROM shortlist WHERE property_id = $property_id");
        
        // Delete property
        mysqli_query($conn, "DELETE FROM properties WHERE id = $property_id");
        
        // Log activity
        if (isset($_SESSION['user_id'])) {
            $admin_id = $_SESSION['user_id'];
            mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, target_type, target_id, details) 
                                 VALUES ($admin_id, 'deleted', 'property', $property_id, 'Property deleted by admin')");
        }
        
        $_SESSION['delete_message'] = "Property deleted successfully!";
    }
}

// Redirect back to properties page
header("Location: admin_properties.php");
exit();
?>