<?php
session_start();
include("db.php");

$user_id = $_SESSION['user_id'];
$property_id = $_GET['id'];

mysqli_query($conn,
    "DELETE FROM shortlist 
     WHERE user_id='$user_id' AND property_id='$property_id'"
);

header("Location: shortlist.php");
exit();
?>