<?php
// Prevent any output before headers
ob_start();

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
    
    // Prevent session fixation after session is started
    if (isset($_COOKIE[session_name()])) {
        session_regenerate_id(true);
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login($requiredType = null) {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit();
    }

    if ($requiredType && ($_SESSION['user_type'] ?? null) !== $requiredType) {
        if (($_SESSION['user_type'] ?? null) === 'buyer') {
            header('Location: /buyer/dashboard.php');
            exit();
        } else if (($_SESSION['user_type'] ?? null) === 'seller') {
            header('Location: /seller/dashboard.php');
            exit();
        }
    }
}

function logout_and_redirect($redirect = '/login.php') {
    // Clear all session variables
    $_SESSION = [];

    // Clear session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            $params['secure'] ?? false,
            $params['httponly'] ?? true
        );
    }

    // Destroy the session
    session_destroy();

    // Clear any additional cookies that might exist
    if (isset($_COOKIE)) {
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', time() - 42000, '/');
        }
    }

    // Log the logout for debugging
    error_log("User logged out, redirecting to: $redirect");

    header('Location: ' . $redirect);
    exit();
}
