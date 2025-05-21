<?php
// Required for security
header('Content-Type: application/json');

// Include Firebase configuration
include_once 'firebase_config.php';

// Return Firebase config as JSON
echo json_encode($firebaseConfig);
?>