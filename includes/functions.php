<?php
include_once __DIR__ . '/firebase_config.php';

function redirectToDashboard() {
    if ($_SESSION['user_type'] === 'buyer') {
        header("Location: /buyer/dashboard.php");
        exit();
    } else if ($_SESSION['user_type'] === 'seller') {
        header("Location: /seller/dashboard.php");
        exit();
    }
}

function showError($message) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

function showSuccess($message) {
    echo '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function getProductCategories() {
    return [
        'Vegetables',
        'Fruits',
        'Rice',
        'Fish',
        'Meat',
        'Other'
    ];
}

function getOrderStatuses() {
    return [
        'Pending',
        'Processing',
        'Out for Delivery',
        'Delivered',
        'Cancelled'
    ];
}

function getStatusBadgeClass($status) {
    switch($status) {
        case 'Pending':
            return 'bg-warning';
        case 'Processing':
            return 'bg-info';
        case 'Out for Delivery':
            return 'bg-primary';
        case 'Delivered':
            return 'bg-success';
        case 'Cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function getFirebaseConfigJSON() {
    global $firebaseConfig;
    return json_encode($firebaseConfig);
}

function generateRandomId($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}
