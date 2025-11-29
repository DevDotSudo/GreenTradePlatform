<?php
header('Content-Type: application/json');
require_once 'firebase_config.php';

echo json_encode($firebaseConfig);
?>
