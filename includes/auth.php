<?php
require_once __DIR__ . '/session.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function ensureUserLoggedIn($requiredType = null) {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit();
    }

    if ($_SESSION['user_type'] === 'seller' && 
        (!isset($_SESSION['seller_approved']) || !$_SESSION['seller_approved'])) {
        header("Location: /seller-approval.php");
        exit();
    }

    if ($requiredType && $_SESSION['user_type'] !== $requiredType) {
        if ($_SESSION['user_type'] === 'buyer') {
            header("Location: /buyer/dashboard.php");
            exit();
        } else if ($_SESSION['user_type'] === 'seller') {
            header("Location: /seller/dashboard.php");
            exit();
        }
    }
}

function createUserSession($userId, $email, $name, $userType, $phone, $address, $emailVerified = false, $sellerApproved = false, $verified = false) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;
    $_SESSION['user_type'] = $userType;
    $_SESSION['phone'] = $phone;
    $_SESSION['address'] = $address;
    $_SESSION['email_verified'] = $emailVerified;
    $_SESSION['verified'] = $verified;

    if ($userType === 'seller') {
        $_SESSION['seller_approved'] = $sellerApproved;
    }
}
