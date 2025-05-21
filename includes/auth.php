<?php
// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is logged in and has the correct user type
function ensureUserLoggedIn($requiredType = null) {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit();
    }
    
    if ($requiredType && $_SESSION['user_type'] !== $requiredType) {
        // Redirect to the appropriate dashboard
        if ($_SESSION['user_type'] === 'buyer') {
            header("Location: /buyer/dashboard.php");
            exit();
        } else if ($_SESSION['user_type'] === 'seller') {
            header("Location: /seller/dashboard.php");
            exit();
        }
    }
}

// Create a session for a logged in user
function createUserSession($userId, $email, $name, $userType, $phone, $address) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;
    $_SESSION['user_type'] = $userType;
    $_SESSION['phone'] = $phone;
    $_SESSION['address'] = $address;
}

// Logout the current user
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("Location: /login.php");
    exit();
}
