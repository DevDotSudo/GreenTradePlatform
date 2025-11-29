<?php
// Test script to verify add product functionality fixes
echo "=== Add Product Fixes Test ===\n\n";

// Test 1: Check if Firebase config is accessible
if (file_exists('includes/firebase_config.php')) {
    echo "✓ Firebase config file exists\n";
    
    include 'includes/firebase_config.php';
    if (isset($firebaseConfig) && !empty($firebaseConfig)) {
        echo "✓ Firebase config loaded successfully\n";
        echo "  - Project ID: " . $firebaseConfig['projectId'] . "\n";
        echo "  - Auth Domain: " . $firebaseConfig['authDomain'] . "\n";
    } else {
        echo "✗ Firebase config is empty or invalid\n";
    }
} else {
    echo "✗ Firebase config file missing\n";
}

// Test 2: Check JavaScript files
$js_files = [
    'assets/js/firebase.js' => 'Firebase initialization',
    'assets/js/main.js' => 'Main functionality and loading overlay',
    'assets/js/dialogs.js' => 'Dialog system for user feedback',
    'seller/add_product.php' => 'Add product form page'
];

echo "\n=== File Existence Check ===\n";
foreach ($js_files as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description ($file)\n";
        
        // Check if key fixes are present
        if ($file === 'seller/add_product.php') {
            $content = file_get_contents($file);
            if (strpos($content, 'firebase.firestore.Timestamp.now()') !== false) {
                echo "  ✓ Firebase Timestamp.now() fix present\n";
            }
            if (strpos($content, '5242880') !== false) {
                echo "  ✓ Image size limit fix present\n";
            }
            if (strpos($content, 'waitForFirebase') !== false) {
                echo "  ✓ Firebase initialization fix present\n";
            }
        }
        
        if ($file === 'assets/js/main.js') {
            $content = file_get_contents($file);
            if (strpos($content, 'LoadingOverlay') !== false) {
                echo "  ✓ Loading overlay system present\n";
            }
        }
        
    } else {
        echo "✗ $description ($file) missing\n";
    }
}

// Test 3: Check session handling
echo "\n=== Session Handling Check ===\n";
if (file_exists('includes/session.php')) {
    echo "✓ Session file exists\n";
    include_once 'includes/session.php';
    
    if (function_exists('session_start')) {
        echo "✓ Session functions available\n";
    }
}

if (file_exists('includes/auth.php')) {
    echo "✓ Auth functions file exists\n";
}

// Test 4: Display recommendations
echo "\n=== Recommendations ===\n";
echo "1. Make sure Firebase project is properly configured\n";
echo "2. Ensure database security rules allow product creation\n";
echo "3. Test with a small image file (< 1MB) first\n";
echo "4. Check browser console for any JavaScript errors\n";
echo "5. Verify user session is properly maintained\n";

echo "\n=== Test Complete ===\n";
?>