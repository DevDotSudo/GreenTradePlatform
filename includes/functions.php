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

function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

function sanitizeInput($data) {
    if (!is_string($data) && !is_numeric($data)) {
        return '';
    }

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function sanitizeEmail($email) {
    $email = sanitizeInput($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid email format');
    }
    return $email;
}

function sanitizePhone($phone) {
    $phone = sanitizeInput($phone);
    // Remove all non-digit characters except + at the beginning
    $phone = preg_replace('/[^\d+]/', '', $phone);
    if (!preg_match('/^\+?[\d]{10,15}$/', $phone)) {
        throw new InvalidArgumentException('Invalid phone number format');
    }
    return $phone;
}

function sanitizeNumeric($value, $min = null, $max = null) {
    if (!is_numeric($value)) {
        throw new InvalidArgumentException('Value must be numeric');
    }

    $num = (float) $value;

    if ($min !== null && $num < $min) {
        throw new InvalidArgumentException("Value must be at least $min");
    }

    if ($max !== null && $num > $max) {
        throw new InvalidArgumentException("Value must be at most $max");
    }

    return $num;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function getProductCategories() {
    return ['Vegetables', 'Fruits', 'Rice', 'Fish', 'Meat', 'Other'];
}

function getOrderStatuses() {
    return ['Pending', 'Processing', 'Out for Delivery', 'Delivered', 'Cancelled'];
}

function getStatusBadgeClass($status) {
    switch($status) {
        case 'Pending': return 'bg-warning';
        case 'Processing': return 'bg-info';
        case 'Out for Delivery': return 'bg-primary';
        case 'Delivered': return 'bg-success';
        case 'Cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function generateRandomId($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
