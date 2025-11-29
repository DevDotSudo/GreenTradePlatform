<?php
require_once __DIR__ . '/includes/session.php';
include 'includes/functions.php';
include 'includes/firebase_config.php';

// If not logged in, redirect to login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: /login.php");
    exit();
}

// If already approved, redirect to dashboard
if (isset($_SESSION['seller_approved']) && $_SESSION['seller_approved']) {
    header("Location: /seller/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awaiting Approval - Green Trade</title>
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
                <p class="auth-subtitle">Seller Account Under Review</p>
            </div>
            <div class="auth-card-body">
                <div id="approval-status" class="alert alert-info text-center">
                    <div class="mb-3">
                        <i data-feather="clock" style="width: 48px; height: 48px;"></i>
                    </div>
                    <h4 class="mb-2">Application Under Review</h4>
                    <p>Your seller account is currently being reviewed by our admin team.</p>
                    <p class="text-muted small">This usually takes 1-2 business days. We'll notify you via email once approved.</p>
                </div>

                <div class="text-center my-4">
                    <button id="check-status-btn" class="btn btn-primary btn-lg w-100 mb-3" onclick="checkApprovalStatus()">
                        <span class="btn-text">Check Status</span>
                        <span class="btn-loading d-none">Checking...</span>
                    </button>
                    
                    <a href="#" onclick="logout()" class="text-muted">Sign out</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="assets/js/dialogs.js"></script>
    <script src="assets/js/firebase.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            
            // Check approval status on page load and redirect if approved
            checkApprovalStatusOnLoad();
        });

        function checkApprovalStatusOnLoad() {
            waitForFirebase(() => {
                if (typeof getCurrentUser === 'function') {
                    getCurrentUser()
                        .then(user => {
                            if (user) {
                                return firebase.firestore().collection('users').doc(user.uid).get()
                                    .then(userDoc => {
                                        if (userDoc.exists) {
                                            const userData = userDoc.data();
                                            if (userData.approved === true) {
                                                // User is approved, redirect to dashboard
                                                return fetch('/includes/create_session.php', {
                                                    method: 'POST',
                                                    headers: { 'Content-Type': 'application/json' },
                                                    body: JSON.stringify({
                                                        userId: user.uid,
                                                        email: userData.email,
                                                        name: userData.name || '',
                                                        phone: userData.phone || '',
                                                        address: userData.address || '',
                                                        userType: userData.userType || 'seller',
                                                        emailVerified: userData.emailVerified || false,
                                                        sellerApproved: true
                                                    })
                                                }).then(r => r.json()).then(res => {
                                                    if (res.success) {
                                                        window.location.href = '/seller/dashboard.php';
                                                    }
                                                });
                                            }
                                        }
                                    });
                            }
                        })
                        .catch(error => {
                            console.error('Error checking approval status on load:', error);
                        });
                }
            });
        }

        function checkApprovalStatus() {
            const btn = document.getElementById('check-status-btn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');

            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            btn.disabled = true;

            // Use getCurrentUser() from assets/js/firebase.js which waits for firebase init
            if (typeof getCurrentUser !== 'function') {
                // Fallback: ensure firebase and currentUser exist
                showToast({
                    title: 'Error',
                    message: 'Authentication helper not available. Please reload the page.',
                    type: 'error'
                });
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
                btn.disabled = false;
                return;
            }

            getCurrentUser()
                .then(user => {
                    if (!user) {
                        showToast({
                            title: 'Not Signed In',
                            message: 'Please sign in to check your approval status. If you signed in in another tab, wait a moment and try again.',
                            type: 'warning'
                        });
                        return;
                    }

                    const uid = user.uid;
                    return firebase.firestore().collection('users').doc(uid).get()
                        .then(userDoc => {
                            if (!userDoc.exists) {
                                throw new Error('User data not found');
                            }
                            const userData = userDoc.data();

                            // Check approval status directly from user document
                            if (userData.approved === true) {
                                // Approved
                                return fetch('/includes/create_session.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        userId: uid,
                                        email: userData.email,
                                        name: userData.name || '',
                                        phone: userData.phone || '',
                                        address: userData.address || '',
                                        userType: userData.userType || 'seller',
                                        emailVerified: userData.emailVerified || false,
                                        sellerApproved: true
                                    })
                                }).then(r => r.json()).then(res => {
                                    if (res.success) {
                                        window.location.href = '/seller/dashboard.php';
                                    } else {
                                        throw new Error(res.message || 'Failed to create session');
                                    }
                                });
                            } else {
                                // Not approved yet
                                showToast({
                                    title: 'Still Under Review',
                                    message: 'Your application is still being reviewed. We\'ll notify you once approved.',
                                    type: 'info'
                                });
                            }
                        });
                })
                .catch(error => {
                    console.error('checkApprovalStatus error:', error);
                    showToast({
                        title: 'Error',
                        message: error.message || 'An unexpected error occurred while checking approval status.',
                        type: 'error'
                    });
                })
                .finally(() => {
                    btnText.classList.remove('d-none');
                    btnLoading.classList.add('d-none');
                    btn.disabled = false;
                });
        }

        function logout() {
            const form = document.getElementById('logout-form');
            if (form) { form.submit(); return; }

            if (typeof firebase !== 'undefined' && firebase.auth) {
                firebase.auth().signOut()
                    .then(() => {
                        fetch('/logout.php', { method: 'POST' }).finally(() => { window.location.href = '/login.php'; });
                    })
                    .catch(error => {
                        console.error('Error signing out:', error);
                        fetch('/logout.php', { method: 'POST' }).finally(() => { window.location.href = '/login.php'; });
                    });
            } else {
                fetch('/logout.php', { method: 'POST' }).finally(() => { window.location.href = '/login.php'; });
            }
        }
    </script>
</body>

</html>