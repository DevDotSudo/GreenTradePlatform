<?php
session_start();
include 'includes/functions.php';
include 'includes/firebase_config.php';

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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-card-header">
                <div class="auth-brand">
                    <span class="auth-icon">🌱</span>
                    <h3 class="auth-title">Green Trade</h3>
                </div>
                <p class="auth-subtitle">Create your account to get started</p>
            </div>
            <div class="auth-card-body">
                <div id="register-error" class="alert alert-error d-none"></div>
                <form id="register-form">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter your email" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" placeholder="Create password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm-password" placeholder="Confirm password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" placeholder="Enter your phone number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" placeholder="Enter your address" rows="2" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Account Type</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" name="user-type" id="buyer" value="buyer" checked>
                                <label for="buyer">
                                    <span class="radio-icon">🛒</span>
                                    <span class="radio-text">
                                        <strong>Buyer</strong>
                                        <small>Purchase fresh products</small>
                                    </span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="user-type" id="seller" value="seller">
                                <label for="seller">
                                    <span class="radio-icon">🏪</span>
                                    <span class="radio-text">
                                        <strong>Seller</strong>
                                        <small>Sell your products</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <span class="btn-text">Create Account</span>
                        <span class="btn-loading d-none">Creating account...</span>
                    </button>
                </form>
                
                <div class="auth-link">
                    <p>Already have an account? <a href="login.php">Sign in here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="assets/js/dialogs.js"></script>
    
    <script>
    // Firebase configuration
    const firebaseConfig = {
        apiKey: "AIzaSyCIjDPMvgKVTpleUCYWtMIu-K6bW1gHJZY",
        authDomain: "greentrade-project.firebaseapp.com",
        projectId: "greentrade-project",
        storageBucket: "greentrade-project.firebasestorage.app",
        messagingSenderId: "582047266659",
        appId: "1:582047266659:web:47054d9178fbd66f0d8556",
        measurementId: "G-M2FMJ35F4K"
    };
    
    if (typeof firebase !== 'undefined') {
        firebase.initializeApp(firebaseConfig);
        console.log('Firebase initialized successfully');
    } else {
        console.error('Firebase SDK not loaded');
    }
    </script>
    <script src="assets/js/auth.js"></script>
    
    <style>
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: var(--space-4);
        background: linear-gradient(135deg, var(--primary-50) 0%, #ffffff 100%);
    }

    .auth-card {
        background: white;
        border-radius: var(--radius-2xl);
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--gray-200);
        width: 100%;
        max-width: 600px;
        overflow: hidden;
        transition: all var(--transition-normal);
    }

    .auth-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-2xl);
    }

    .auth-card-header {
        background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
        color: white;
        padding: var(--space-8) var(--space-6);
        text-align: center;
        position: relative;
    }

    .auth-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-3);
        margin-bottom: var(--space-4);
    }

    .auth-icon {
        font-size: 2.5rem;
    }

    .auth-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    .auth-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0;
    }

    .auth-card-body {
        padding: var(--space-8) var(--space-6);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-4);
    }

    .radio-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--space-3);
        margin-top: var(--space-2);
    }

    .radio-option {
        position: relative;
    }

    .radio-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .radio-option label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-4);
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-lg);
        cursor: pointer;
        transition: all var(--transition-fast);
        background: white;
        text-align: center;
    }

    .radio-option input[type="radio"]:checked + label {
        background: var(--primary-50);
        border-color: var(--primary-500);
        color: var(--primary-700);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .radio-option label:hover {
        border-color: var(--primary-300);
        transform: translateY(-1px);
    }

    .radio-icon {
        font-size: 2rem;
    }

    .radio-text {
        display: flex;
        flex-direction: column;
        gap: var(--space-1);
    }

    .radio-text strong {
        font-weight: 600;
    }

    .radio-text small {
        font-size: 0.75rem;
        opacity: 0.7;
    }

    .auth-link {
        text-align: center;
        margin-top: var(--space-6);
        padding-top: var(--space-6);
        border-top: 1px solid var(--gray-200);
    }

    .auth-link p {
        color: var(--gray-600);
        margin: 0;
    }

    .auth-link a {
        color: var(--primary-600);
        text-decoration: none;
        font-weight: 600;
        transition: color var(--transition-fast);
    }

    .auth-link a:hover {
        color: var(--primary-700);
    }

    .btn-loading {
        display: none;
    }

    .btn.loading .btn-text {
        display: none;
    }

    .btn.loading .btn-loading {
        display: inline;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .auth-container {
            padding: var(--space-3);
        }

        .auth-card-header {
            padding: var(--space-6) var(--space-4);
        }

        .auth-card-body {
            padding: var(--space-6) var(--space-4);
        }

        .auth-title {
            font-size: 1.75rem;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .radio-group {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .auth-card-header {
            padding: var(--space-4) var(--space-3);
        }

        .auth-card-body {
            padding: var(--space-4) var(--space-3);
        }

        .auth-title {
            font-size: 1.5rem;
        }

        .auth-icon {
            font-size: 2rem;
        }
    }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('register-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                
                // Show loading state
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm-password').value;
                const phone = document.getElementById('phone').value;
                const address = document.getElementById('address').value;
                const userType = document.querySelector('input[name="user-type"]:checked').value;
                
                if (password !== confirmPassword) {
                    showToast({
                        title: 'Error',
                        message: 'Passwords do not match. Please try again.',
                        type: 'error'
                    });
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    return;
                }
                
                registerUser(email, password, name, phone, address, userType)
                    .finally(() => {
                        // Reset loading state
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                    });
            });
        });
    </script>
</body>
</html>