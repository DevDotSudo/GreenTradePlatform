<?php
session_start();
include 'includes/functions.php';

// Redirect to appropriate dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'buyer') {
        header("Location: buyer/dashboard.php");
        exit();
    } else if ($_SESSION['user_type'] === 'seller') {
        header("Location: seller/dashboard.php");
        exit();
    }
}

// Otherwise, redirect to login page
header("Location: login.php");
exit();
?>
