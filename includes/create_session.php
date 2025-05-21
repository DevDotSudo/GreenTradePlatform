<?php
session_start();
header('Content-Type: application/json');

// Get the JSON data sent in the request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Check if data is valid
if (!$data || !isset($data['userId']) || !isset($data['email']) || !isset($data['name']) || !isset($data['userType'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data provided'
    ]);
    exit;
}

// Create session variables
$_SESSION['user_id'] = $data['userId'];
$_SESSION['email'] = $data['email'];
$_SESSION['name'] = $data['name'];
$_SESSION['phone'] = $data['phone'] ?? '';
$_SESSION['address'] = $data['address'] ?? '';
$_SESSION['user_type'] = $data['userType'];

// Send success response
echo json_encode([
    'success' => true,
    'message' => 'Session created successfully'
]);
?>