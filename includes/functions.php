<?php
// Include Firebase config
include_once __DIR__ . '/firebase_config.php';

// Redirect to appropriate dashboard based on user type
function redirectToDashboard() {
    if ($_SESSION['user_type'] === 'buyer') {
        header("Location: /buyer/dashboard.php");
        exit();
    } else if ($_SESSION['user_type'] === 'seller') {
        header("Location: /seller/dashboard.php");
        exit();
    }
}

// Display error message
function showError($message) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

// Display success message
function showSuccess($message) {
    echo '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

// Format price to Philippine Peso
function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Get product categories
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

// Get order status options
function getOrderStatuses() {
    return [
        'Pending',
        'Processing',
        'Out for Delivery',
        'Delivered',
        'Cancelled'
    ];
}

// Get status badge class
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

// Get Firebase config for JavaScript
function getFirebaseConfigJSON() {
    global $firebaseConfig;
    return json_encode($firebaseConfig);
}

// Generate a random ID
function generateRandomId($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}
