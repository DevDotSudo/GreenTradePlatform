async function registerUser(email, password, name, phone, address, userType) {
    try {
        // Ensure auth state persists across tabs/popups
        try {
            await firebase.auth().setPersistence(firebase.auth.Auth.Persistence.LOCAL);
        } catch (pErr) {
            console.warn('Could not set auth persistence:', pErr);
        }
        const userCredential = await firebase.auth().createUserWithEmailAndPassword(email, password);
        const user = userCredential.user;

        await user.sendEmailVerification();

        const userData = {
            userId: user.uid,
            email: email,
            name: name,
            phone: phone,
            address: address,
            userType: userType,
            emailVerified: false,
            createdAt: firebase.firestore.FieldValue.serverTimestamp()
        };

        if (userType === 'seller') {
            userData.approved = false;
            userData.approvalStatus = 'pending';
        }

        // Save user data to Firestore
        await firebase.firestore().collection('users').doc(user.uid).set(userData);

 // Create PHP session
const sessionResponse = await fetch('/includes/create_session.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        userId: user.uid,
        email: email,
        name: name,
        phone: phone,
        address: address,
        userType: userType,
        emailVerified: user.emailVerified || false,
        sellerApproved: userType === 'seller' ? false : true
    })
});

const sessionResult = await sessionResponse.json();
if (!sessionResult.success) {
    throw new Error(sessionResult.message || 'Failed to create session');
}

console.log('Session created successfully for user:', user.uid);

        showToast({
            title: 'Account Created',
            message: 'Account created successfully. A verification email was sent — please verify your email.',
            type: 'success',
            duration: 3000
        });

        // Redirect to the verify page so user can complete verification instructions.
        // We include the user type so the verify page can redirect appropriately after verification.
        setTimeout(() => {
            window.location.href = '/verify-email.php?type=' + encodeURIComponent(userType);
        }, 1200);

    } catch (error) {
        console.error('Registration error:', error);
        let errorMessage = 'An error occurred during registration.';

        switch (error.code) {
            case 'auth/email-already-in-use':
                errorMessage = 'This email is already registered. Please use a different email or try logging in.';
                break;
            case 'auth/invalid-email':
                errorMessage = 'Please enter a valid email address.';
                break;
            case 'auth/weak-password':
                errorMessage = 'Password should be at least 6 characters long.';
                break;
            case 'auth/operation-not-allowed':
                errorMessage = 'Account registration is currently unavailable. Please contact support.';
                break;
            default:
                errorMessage = 'Registration failed. Please try again or contact support.';
        }

        showToast({
            title: 'Registration Failed',
            message: errorMessage,
            type: 'error'
        });
        throw error;
    }
}

async function loginUser(email, password) {
    try {
        // Ensure auth state persists across tabs/popups
        try {
            await firebase.auth().setPersistence(firebase.auth.Auth.Persistence.LOCAL);
        } catch (pErr) {
            console.warn('Could not set auth persistence:', pErr);
        }
        // Sign in with Firebase
        const userCredential = await firebase.auth().signInWithEmailAndPassword(email, password);
        const user = userCredential.user;

        // Get user data from Firestore
        const userDoc = await firebase.firestore().collection('users').doc(user.uid).get();

        if (!userDoc.exists) {
            throw new Error('User profile not found');
        }

        const userData = userDoc.data();
        const userType = userData.userType;

        // If not email-verified, allow login but remind the user to verify
        if (!user.emailVerified) {
            // Create PHP session with unverified status
            await createSession(user.uid, userData, false, userType === 'seller' ? userData.approved : true);
            // Send verification email again (non-blocking)
            user.sendEmailVerification().catch(() => {});
            showToast({
                title: 'Email Not Verified',
                message: 'Please verify your email address. You can continue to use the site while unverified.',
                type: 'warning',
                duration: 3000
            });
            // do not redirect to verify page; continue to dashboard below
        }

        // For sellers, check approval status
        if (userType === 'seller' && !userData.approved) {
            // Create PHP session with verified status reflecting the user
            await createSession(user.uid, userData, user.emailVerified || false, false);
            showToast({
                title: 'Account Under Review',
                message: 'Your seller account is pending admin approval. You will be notified once approved.',
                type: 'info',
                duration: 3000
            });
            setTimeout(() => {
                window.location.href = '/seller-approval.php';
            }, 1500);
            return;
        }

        // Create PHP session and redirect to dashboard
        await createSession(user.uid, userData, user.emailVerified || false, userType === 'seller' ? userData.approved : true);
        
        showToast({
            title: 'Welcome Back!',
            message: `Welcome back, ${userData.name}!`,
            type: 'success',
            duration: 2000
        });

        setTimeout(() => {
            window.location.href = userType === 'seller' ? '/seller/dashboard.php' : '/buyer/dashboard.php';
        }, 2000);

    } catch (error) {
        console.error('Login error:', error);
        let errorMessage = 'An error occurred during login.';

        switch (error.code) {
            case 'auth/user-not-found':
                errorMessage = 'No account found with this email. Please check your email or register.';
                break;
            case 'auth/wrong-password':
                errorMessage = 'Incorrect password. Please try again.';
                break;
            case 'auth/invalid-email':
                errorMessage = 'Please enter a valid email address.';
                break;
            case 'auth/user-disabled':
                errorMessage = 'This account has been disabled. Please contact support.';
                break;
            case 'auth/too-many-requests':
                errorMessage = 'Too many failed login attempts. Please try again later.';
                break;
            default:
                errorMessage = 'Login failed. Please try again or contact support.';
        }

        showToast({
            title: 'Login Failed',
            message: errorMessage,
            type: 'error'
        });
        throw error;
    }
}

async function createSession(userId, userData, emailVerified, sellerApproved) {
    try {
        // First validate the data client-side - be more lenient with missing fields
        if (!userId || !userData) {
            throw new Error('Missing required user data');
        }

        // If essential fields are missing, try to get current user data from Firebase
        if (!userData.email || !userData.name || !userData.userType) {
            console.log('Missing some user data fields, fetching from Firebase...', userData);
            
            // Get current user to fill in missing data
            const currentUser = await getCurrentUser();
            if (currentUser) {
                // Merge current user data with provided data
                userData = Object.assign({
                    email: currentUser.email || userData.email || '',
                    name: currentUser.displayName || userData.name || '',
                    userType: userData.userType || 'buyer', // default to buyer if not specified
                    phone: userData.phone || '',
                    address: userData.address || ''
                }, userData);
            }
        }

        // Final validation with specific error messages
        const missingFields = [];
        if (!userData.email) missingFields.push('email');
        if (!userData.name) missingFields.push('name');
        if (!userData.userType) missingFields.push('userType');
        
        if (missingFields.length > 0) {
            console.error('Missing required fields:', missingFields, 'User data:', userData);
            throw new Error(`Missing required user data: ${missingFields.join(', ')}`);
        }

        const response = await fetch('/includes/create_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                userId: userId,
                email: userData.email,
                name: userData.name,
                phone: userData.phone || '',
                address: userData.address || '',
                userType: userData.userType,
                emailVerified: emailVerified,
                sellerApproved: sellerApproved
            }),
            credentials: 'same-origin'
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // Log the actual response for debugging
            const text = await response.text();
            console.error('Non-JSON response received:', text);
            throw new Error('Server returned non-JSON response');
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to create session');
        }

        return result;
    } catch (error) {
        console.error('createSession error:', error);
        throw error;
    }
}

function logoutUser() {
    showConfirm({
        title: 'Confirm Logout',
        message: 'Are you sure you want to log out?',
        type: 'warning',
        confirmText: 'Logout',
        cancelText: 'Cancel',
        onConfirm: () => {
            firebase.auth().signOut()
                .then(() => {
                    showToast({
                        title: 'Logged Out',
                        message: 'You have been successfully logged out.',
                        type: 'success',
                        duration: 2000
                    });

                    // Submit server-side logout via POST if a logout form exists, otherwise POST via fetch
                    setTimeout(() => {
                        const form = document.getElementById('logout-form');
                        if (form) {
                            form.submit();
                        } else {
                            fetch('/logout.php', { method: 'POST' })
                                .then(() => { window.location.href = '/login.php'; })
                                .catch(() => { window.location.href = '/login.php'; });
                        }
                    }, 800);
                })
                .catch((error) => {
                    console.error("Logout error:", error);
                    showToast({
                        title: 'Error',
                        message: 'Error logging out. Please try again.',
                        type: 'error'
                    });
                    // attempt server-side logout anyway
                    const form = document.getElementById('logout-form');
                    if (form) form.submit();
                    else fetch('/logout.php', { method: 'POST' }).finally(() => { window.location.href = '/login.php'; });
                });
        }
    });
}

function showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.classList.remove('d-none');
    }
}

function validateForm(formData) {
    const errors = [];

    if (!formData.email || !formData.email.includes('@')) {
        errors.push('Please enter a valid email address.');
    }

    if (!formData.password || formData.password.length < 6) {
        errors.push('Password must be at least 6 characters long.');
    }

    if (!formData.name || formData.name.trim().length < 2) {
        errors.push('Please enter your full name.');
    }

    if (formData.phone && !/^[\\+]?[1-9][\\d]{0,15}$/.test(formData.phone.replace(/\\s/g, ''))) {
        errors.push('Please enter a valid phone number.');
    }

    if (!formData.address || formData.address.trim().length < 10) {
        errors.push('Please enter your complete address.');
    }

    return errors;
}

function showValidationErrors(errors) {
    if (errors.length === 1) {
        showToast({
            title: 'Validation Error',
            message: errors[0],
            type: 'warning'
        });
    } else {
        showAlert({
            title: 'Validation Errors',
            message: errors.join('\n'),
            type: 'warning',
            buttonText: 'OK'
        });
    }
}

// Expose functions on the global window object for non-module scripts
if (typeof window !== 'undefined') {
    window.registerUser = registerUser;
    window.loginUser = loginUser;
    window.logoutUser = logoutUser;
    window.createSession = createSession;
    window.showError = showError;
    window.validateForm = validateForm;
    window.showValidationErrors = showValidationErrors;
    // also provide a namespace
    window.auth = {
        registerUser,
        loginUser,
        logoutUser,
        createSession,
        showError,
        validateForm,
        showValidationErrors
    };
}

