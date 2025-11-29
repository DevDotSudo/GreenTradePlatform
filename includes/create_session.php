<?php
// Prevent any output before headers
ob_start();

// Disable error display but enable error logging
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth.php';

// Set JSON content type
header('Content-Type: application/json');

if (isset($_GET['verified'])) {
    $_SESSION['email_verified'] = true;
    header('Location: ' . ($_SESSION['user_type'] === 'seller' ? '/seller-approval.php' : '/buyer/dashboard.php'));
    exit;
}

if (isset($_GET['approved'])) {
    $_SESSION['seller_approved'] = true;
    header('Location: /seller/dashboard.php');
    exit;
}

// Get and decode JSON data
$jsonData = file_get_contents('php://input');
if ($jsonData === false) {
    error_log("Create session error: Failed to read request data");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to read request data'
    ]);
    exit;
}

$data = json_decode($jsonData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Create session error: Invalid JSON - " . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data provided'
    ]);
    exit;
}

// Log incoming data for debugging
error_log("Create session request received. Data: " . json_encode($data));

// Validate required fields
if (!$data || !isset($data['userId']) || !isset($data['email']) || !isset($data['name']) || !isset($data['userType'])) {
    error_log("Create session error: Missing required fields");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Validate email format
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    error_log("Create session error: Invalid email format");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Validate user ID format
if (strlen($data['userId']) < 10) {
    error_log("Create session error: Invalid user ID format");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user ID format'
    ]);
    exit;
}

// Sanitize input data
$data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$data['phone'] = isset($data['phone']) ? preg_replace('/[^0-9+]/', '', $data['phone']) : '';
$data['name'] = strip_tags(trim($data['name']));
$data['address'] = isset($data['address']) ? strip_tags(trim($data['address'])) : '';

// Create user session
try {
    createUserSession(
        $data['userId'],
        $data['email'],
        $data['name'],
        $data['userType'],
        $data['phone'] ?? '',
        $data['address'] ?? '',
        $data['emailVerified'] ?? false,
        $data['sellerApproved'] ?? false,
        $data['verified'] ?? false
    );

    error_log("Session created successfully for user: " . $data['userId'] . " (" . $data['userType'] . ")");

    echo json_encode([
        'success' => true,
        'message' => 'Session created successfully',
        'emailVerified' => $_SESSION['email_verified'] ?? false,
        'sellerApproved' => $_SESSION['seller_approved'] ?? false
    ]);
} catch (Exception $e) {
    error_log("Create session error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create session'
    ]);
}
