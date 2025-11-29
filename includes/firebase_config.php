<?php
// Load Firebase config from environment variables for security
$firebaseConfig = [
    'apiKey' => getenv('FIREBASE_API_KEY') ?: "AIzaSyCIjDPMvgKVTpleUCYWtMIu-K6bW1gHJZY",
    'authDomain' => getenv('FIREBASE_AUTH_DOMAIN') ?: "greentrade-project.firebaseapp.com",
    'projectId' => getenv('FIREBASE_PROJECT_ID') ?: "greentrade-project",
    'storageBucket' => getenv('FIREBASE_STORAGE_BUCKET') ?: "greentrade-project.appspot.com",
    'messagingSenderId' => getenv('FIREBASE_MESSAGING_SENDER_ID') ?: "582047266659",
    'appId' => getenv('FIREBASE_APP_ID') ?: "1:582047266659:web:47054d9178fbd66f0d8556",
    'measurementId' => getenv('FIREBASE_MEASUREMENT_ID') ?: "G-M2FMJ35F4K"
];

// Log warning if using fallback values (not recommended for production)
if (getenv('FIREBASE_API_KEY') === false) {
    error_log("WARNING: Using hardcoded Firebase config. Set environment variables for production security.");
}
