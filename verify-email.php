<?php
require_once __DIR__ . '/includes/session.php';
include 'includes/functions.php';
include 'includes/firebase_config.php';

// If not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// If already verified, redirect to dashboard
if (isset($_SESSION['email_verified']) && $_SESSION['email_verified']) {
    redirectToDashboard();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Green Trade</title>
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
                <p class="auth-subtitle">Please verify your email address</p>
            </div>
            <div class="auth-card-body">
                <div id="verification-status" class="alert alert-info text-center">
                    <div class="mb-3">
                        <i data-feather="mail" style="width: 48px; height: 48px;"></i>
                    </div>
                    <h4 class="mb-2">Check your inbox</h4>
                    <p>We've sent a verification link to <strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong></p>
                    <p class="text-muted small">Click the link in the email to verify your account.</p>
                </div>

                <div class="text-center my-4">
                    <button id="resend-btn" class="btn btn-outline-primary btn-lg w-100" onclick="resendVerification()">
                        <span class="btn-text">Resend Verification Email</span>
                        <span class="btn-loading d-none">Sending...</span>
                    </button>
                </div>

                <div class="text-center">
                    <button id="check-btn" class="btn btn-primary btn-lg w-100 mb-3" onclick="checkVerification()">
                        <span class="btn-text">I've Verified My Email</span>
                        <span class="btn-loading d-none">Checking...</span>
                    </button>
                    
                    <a href="#" onclick="logout()" class="text-muted">Sign out</a>
                </div>
                <div id="not-signed-in" class="alert alert-warning text-center d-none mt-3">
                    <p class="mb-1"><strong>Not signed in (client)</strong></p>
                    <p class="small mb-2">It looks like your browser isn't currently signed in with Firebase. If you used a different tab or browser during registration, please sign in below to complete verification.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="/login.php" class="btn btn-sm btn-outline-primary">Sign in / Return to Login</a>
                        <button id="popup-signin" class="btn btn-sm btn-primary" onclick="openLoginPopup()">Sign in now (popup)</button>
                    </div>
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
        let firebaseUser = null;

        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();

            // Wait for firebase to initialize and get current user
            if (typeof getCurrentUser === 'function') {
                getCurrentUser().then(user => {
                    firebaseUser = user;
                    if (!firebaseUser) {
                        // Show inline not-signed-in banner (less intrusive than a toast)
                        const banner = document.getElementById('not-signed-in');
                        if (banner) banner.classList.remove('d-none');
                        // keep buttons available so user can follow instructions; clicking them will show a helpful error
                    } else {
                        // if session email differs from firebase email, show a small notice
                        const sessionEmail = '<?php echo addslashes($_SESSION['email'] ?? ''); ?>';
                        if (sessionEmail && sessionEmail.toLowerCase() !== (firebaseUser.email || '').toLowerCase()) {
                            showToast({
                                title: 'Email Mismatch',
                                message: 'The signed-in Firebase account does not match your session email. Please sign in with the same email.',
                                type: 'warning'
                            });
                        }
                    }
                }).catch(err => {
                    console.error('Error getting firebase user:', err);
                });

                // also listen for auth state changes so if the user signs in in another tab we update the UI
                if (typeof firebase !== 'undefined' && firebase.auth) {
                    firebase.auth().onAuthStateChanged(function(user) {
                        const banner = document.getElementById('not-signed-in');
                        if (user) {
                            firebaseUser = user;
                            if (banner) banner.classList.add('d-none');
                            // If user signs in, reload and auto-complete if already verified
                            user.reload().then(() => {
                                if (user.emailVerified) {
                                    // Update Firestore and server session
                                    firebase.firestore().collection('users').doc(user.uid).update({
                                        emailVerified: true,
                                        verifiedAt: firebase.firestore.FieldValue.serverTimestamp()
                                    }).then(() => {
                                        window.location.href = '/includes/create_session.php?verified=true';
                                    }).catch(err => {
                                        console.error('Error updating user verification after auth change:', err);
                                    });
                                } else {
                                    showToast({
                                        title: 'Signed In',
                                        message: 'Signed in successfully. Please check your email for the verification link or click "Resend Verification Email".',
                                        type: 'success'
                                    });
                                }
                            }).catch(err => console.error('Error reloading user after auth change:', err));
                        } else {
                            if (banner) banner.classList.remove('d-none');
                        }
                    });
                }

                let loginPopup = null;
                function openLoginPopup() {
                    if (loginPopup && !loginPopup.closed) {
                        loginPopup.focus();
                        return;
                    }
                    const w = 500, h = 700;
                    const left = (screen.width / 2) - (w / 2);
                    const top = (screen.height / 2) - (h / 2);
                    loginPopup = window.open('/login.php', 'loginPopup', `width=${w},height=${h},left=${left},top=${top}`);

                    // Poll for popup closed and then attempt to get current user
                    const poll = setInterval(() => {
                        if (!loginPopup || loginPopup.closed) {
                            clearInterval(poll);
                            // attempt to refresh firebase user
                            if (typeof getCurrentUser === 'function') {
                                getCurrentUser().then(u => {
                                    if (u) {
                                        // onAuthStateChanged will handle the rest
                                    }
                                }).catch(() => {});
                            }
                        }
                    }, 1000);
                }
            }
        });

        function resendVerification() {
            const btn = document.getElementById('resend-btn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');

            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            btn.disabled = true;

            (typeof getCurrentUser === 'function' ? getCurrentUser() : Promise.resolve(firebase.auth().currentUser))
                .then(user => {
                    if (!user) throw new Error('No authenticated user found. Please sign in first.');
                    return user.sendEmailVerification();
                })
                .then(() => {
                    showToast({
                        title: 'Email Sent',
                        message: 'Verification email has been sent. Please check your inbox.',
                        type: 'success'
                    });
                })
                .catch(error => {
                    showToast({
                        title: 'Error',
                        message: error.message || String(error),
                        type: 'error'
                    });
                })
                .finally(() => {
                    btnText.classList.remove('d-none');
                    btnLoading.classList.add('d-none');
                    btn.disabled = false;
                });
        }

        function checkVerification() {
            const btn = document.getElementById('check-btn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');

            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            btn.disabled = true;

            (typeof getCurrentUser === 'function' ? getCurrentUser() : Promise.resolve(firebase.auth().currentUser))
                .then(user => {
                    if (!user) throw new Error('No authenticated user found. Please sign in first.');
                    return user.reload().then(() => user);
                })
                .then(user => {
                    if (user.emailVerified) {
                        // Update verified status in Firestore then create/update server session by POSTing the user profile
                        return firebase.firestore().collection('users').doc(user.uid).update({
                            emailVerified: true,
                            verifiedAt: firebase.firestore.FieldValue.serverTimestamp()
                        }).then(() => {
                            // fetch user profile
                            return firebase.firestore().collection('users').doc(user.uid).get();
                        }).then(doc => {
                            if (!doc.exists) throw new Error('User profile not found');
                            const data = doc.data();
                            // Post profile to server to create session
                            return fetch('/includes/create_session.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    userId: user.uid,
                                    email: data.email || user.email,
                                    name: data.name || '',
                                    phone: data.phone || '',
                                    address: data.address || '',
                                    userType: data.userType || 'buyer',
                                    emailVerified: true,
                                    sellerApproved: data.approved || false
                                })
                            }).then(res => res.json()).then(result => {
                                if (result.success) {
                                    // redirect according to server logic
                                    if (result.sellerApproved) window.location.href = '/seller/dashboard.php';
                                    else if (result.emailVerified && (data.userType || 'buyer') === 'seller') window.location.href = '/seller-approval.php';
                                    else window.location.href = '/buyer/dashboard.php';
                                } else {
                                    throw new Error(result.message || 'Could not create session');
                                }
                            });
                        }).catch(err => {
                            console.error('Verification flow error:', err);
                            showToast({ title: 'Error', message: String(err), type: 'error' });
                        });
                    } else {
                        showToast({
                            title: 'Not Verified',
                            message: 'Please check your email and click the verification link.',
                            type: 'warning'
                        });
                    }
                })
                .catch(error => {
                    showToast({
                        title: 'Error',
                        message: error.message || String(error),
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
            // prefer submitting logout form if present (server expects POST)
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