<?php
header('Content-Type: application/json');

require_once 'firebase_config.php';

global $firebaseConfig;
echo json_encode($firebaseConfig);
?>