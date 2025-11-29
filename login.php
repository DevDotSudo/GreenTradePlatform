<?php
require_once __DIR__ . '/includes/session.php';
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
    <title>Login - Green Trade</title>
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
                <p class="auth-subtitle">Welcome back! Please sign in to your account.</p>
            </div>
            <div class="auth-card-body">
                <div id="login-error" class="alert alert-error d-none"></div>
                <form id="login-form">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="Enter your password" required>
                        <div class="forgot-password">
                            <a href="#" onclick="showForgotPassword()">Forgot password?</a>
                        </div>
                    </div>


                    <button type="submit" class="btn btn-primary btn-lg w-100" id="login-btn">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Signing in...
                        </span>
                    </button>
                </form>

                <div class="auth-link">
                    <p>Don't have an account? <a href="/register.php">Create one here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="assets/js/dialogs.js"></script>

    <script>
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
        /* Modern Minimal Login Design */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-6);
            background: linear-gradient(135deg, var(--primary-50) 0%, var(--neutral-50) 100%);
        }

        .auth-card {
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--neutral-200);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .auth-card-header {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            color: white;
            padding: var(--space-8) var(--space-6);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-3);
            margin-bottom: var(--space-4);
            position: relative;
            z-index: 1;
        }

        .auth-icon {
            font-size: 2.5rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .auth-title {
            font-size: 1.875rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.025em;
        }

        .auth-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 400;
        }

        .auth-card-body {
            padding: var(--space-8) var(--space-6);
        }

        .form-group {
            margin-bottom: var(--space-6);
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--neutral-700);
            margin-bottom: var(--space-2);
            font-size: 0.875rem;
            letter-spacing: -0.01em;
        }

        .form-control {
            width: 100%;
            padding: var(--space-4);
            font-size: 1rem;
            line-height: 1.5;
            color: var(--neutral-900);
            background: var(--neutral-50);
            border: 2px solid var(--neutral-200);
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-500);
            background: white;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
            transform: translateY(-1px);
        }

        .form-control:hover {
            border-color: var(--neutral-300);
        }

        .forgot-password {
            text-align: right;
            margin-top: var(--space-3);
        }

        .forgot-password a {
            color: var(--neutral-600);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all var(--transition-fast);
        }

        .forgot-password a:hover {
            color: var(--primary-600);
            text-decoration: underline;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            border: none;
            color: white;
            font-weight: 600;
            letter-spacing: 0.025em;
            transition: all var(--transition-fast);
            box-shadow: 0 4px 14px 0 rgba(34, 197, 94, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(34, 197, 94, 0.4);
        }

        .auth-link {
            text-align: center;
            margin-top: var(--space-8);
            padding-top: var(--space-6);
            border-top: 1px solid var(--neutral-200);
        }

        .auth-link p {
            color: var(--neutral-600);
            margin: 0;
            font-size: 0.875rem;
        }

        .auth-link a {
            color: var(--primary-600);
            text-decoration: none;
            font-weight: 600;
            transition: all var(--transition-fast);
        }

        .auth-link a:hover {
            color: var(--primary-700);
            text-decoration: underline;
        }

        .btn-loading {
            display: none;
        }

        .btn.loading .btn-text {
            display: none;
        }

        .btn.loading .btn-loading {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Account Type Selection - Modern Design */
        .account-type-section {
            margin: var(--space-6) 0;
        }

        .account-type-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--neutral-700);
            margin-bottom: var(--space-3);
            text-align: center;
        }

        .account-type-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-3);
        }

        .account-type-option {
            position: relative;
            background: var(--neutral-50);
            border: 2px solid var(--neutral-200);
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            cursor: pointer;
            transition: all var(--transition-fast);
            text-align: center;
        }

        .account-type-option:hover {
            border-color: var(--primary-300);
            background: var(--primary-50);
            transform: translateY(-2px);
        }

        .account-type-option.selected {
            background: var(--primary-50);
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        .account-type-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .account-type-icon {
            font-size: 1.75rem;
            margin-bottom: var(--space-2);
            display: block;
        }

        .account-type-label {
            font-weight: 600;
            color: var(--neutral-900);
            font-size: 0.875rem;
            margin-bottom: var(--space-1);
        }

        .account-type-desc {
            font-size: 0.75rem;
            color: var(--neutral-600);
            line-height: 1.4;
        }

        /* Error States */
        .alert-error {
            background: var(--error-50);
            color: var(--error-600);
            border: 1px solid var(--error-200);
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            margin-bottom: var(--space-6);
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Loading Animation */
        @keyframes shimmer {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }

        .loading-shimmer {
            background: linear-gradient(90deg, var(--neutral-200) 25%, var(--neutral-100) 50%, var(--neutral-200) 75%);
            background-size: 200px 100%;
            animation: shimmer 1.5s infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-container {
                padding: var(--space-4);
            }

            .auth-card-header {
                padding: var(--space-6) var(--space-4);
            }

            .auth-card-body {
                padding: var(--space-6) var(--space-4);
            }

            .auth-title {
                font-size: 1.625rem;
            }

            .account-type-grid {
                grid-template-columns: 1fr;
                gap: var(--space-2);
            }
        }

        @media (max-width: 480px) {
            .auth-card-header {
                padding: var(--space-5) var(--space-3);
            }

            .auth-card-body {
                padding: var(--space-5) var(--space-3);
            }

            .auth-title {
                font-size: 1.5rem;
            }

            .auth-icon {
                font-size: 2rem;
            }

            .account-type-option {
                padding: var(--space-3);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('login-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('login-btn');
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');

                btnText.classList.add('d-none');
                btnLoading.classList.remove('d-none');
                submitBtn.disabled = true;

                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;

                loginUser(email, password)
                    .catch(error => {
                        console.error('Login error:', error);
                        const errorDiv = document.getElementById('login-error');
                        errorDiv.textContent = error.message || 'Login failed. Please try again.';
                        errorDiv.classList.remove('d-none');
                    })
                    .finally(() => {
                        btnText.classList.remove('d-none');
                        btnLoading.classList.add('d-none');
                        submitBtn.disabled = false;
                    });
            });
        });

        function showForgotPassword() {
            showAlert({
                title: 'Forgot Password',
                message: 'Please contact support to reset your password.',
                type: 'info',
                buttonText: 'OK'
            });
        }
    </script>
</body>

</html>