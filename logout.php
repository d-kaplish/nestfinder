<?php
session_start();
include "db.php";  

if (isset($_COOKIE['remember_token'])) {
    $token = mysqli_real_escape_string($conn, $_COOKIE['remember_token']);
    mysqli_query($conn, "UPDATE users SET remember_token = NULL WHERE remember_token = '$token'");
    setcookie('remember_token', '', time() - 3600, "/");
}

session_unset();
session_destroy();
header("Location: index.php");
exit();
?>