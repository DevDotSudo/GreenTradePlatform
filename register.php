<?php
session_start();
include 'includes/functions.php';
include 'includes/firebase_config.php';

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    redirectToDashboard();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h3>Green Trade</h3>
                        <p class="mb-0">Create a new account</p>
                    </div>
                    <div class="card-body">
                        <div id="register-error" class="alert alert-danger d-none"></div>
                        <form id="register-form">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm-password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm-password" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Account Type</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user-type" id="buyer" value="buyer" checked>
                                    <label class="form-check-label" for="buyer">
                                        Buyer
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user-type" id="seller" value="seller">
                                    <label class="form-check-label" for="seller">
                                        Seller
                                    </label>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Register</button>
                            </div>
                        </form>
                        <div class="mt-3 text-center">
                            <p>Already have an account? <a href="login.php" class="text-success">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    
    <script>
    // Firebase configuration
    const firebaseConfig = {
        apiKey: "<?php echo getenv('FIREBASE_API_KEY'); ?>",
        authDomain: "greentrade-project.firebaseapp.com",
        projectId: "greentrade-project",
        storageBucket: "greentrade-project.firebasestorage.app",
        messagingSenderId: "582047266659",
        appId: "1:582047266659:web:47054d9178fbd66f0d8556",
        measurementId: "G-M2FMJ35F4K"
    };
    
    // Initialize Firebase
    if (typeof firebase !== 'undefined') {
        firebase.initializeApp(firebaseConfig);
        console.log('Firebase initialized successfully');
    } else {
        console.error('Firebase SDK not loaded');
    }
    </script>
    <script src="assets/js/auth.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Register form submission
            document.getElementById('register-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm-password').value;
                const phone = document.getElementById('phone').value;
                const address = document.getElementById('address').value;
                const userType = document.querySelector('input[name="user-type"]:checked').value;
                
                // Password confirmation check
                if (password !== confirmPassword) {
                    showError('register-error', 'Passwords do not match');
                    return;
                }
                
                registerUser(email, password, name, phone, address, userType);
            });
        });
    </script>
</body>
</html>
