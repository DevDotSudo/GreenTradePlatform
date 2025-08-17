<?php
session_start();
include 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'buyer') {
        header("Location: buyer/dashboard.php");
        exit();
    } else if ($_SESSION['user_type'] === 'seller') {
        header("Location: seller/dashboard.php");
        exit();
    }
}

header("Location: login.php");
exit();
