<?php
// Required for security
header('Content-Type: application/json');

// Include Firebase configuration
require_once 'firebase_config.php';

// Return Firebase config as JSON
global $firebaseConfig;
echo json_encode($firebaseConfig);
?>