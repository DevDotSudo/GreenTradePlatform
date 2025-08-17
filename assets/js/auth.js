function registerUser(email, password, name, phone, address, userType) {
    firebase.auth().createUserWithEmailAndPassword(email, password)
        .then((userCredential) => {
            const user = userCredential.user;
            return firebase.firestore().collection('users').doc(user.uid).set({
                email: email,
                name: name,
                phone: phone,
                address: address,
                userType: userType,
                createdAt: firebase.firestore.FieldValue.serverTimestamp()
            })
            .then(() => {
                return fetch('/includes/create_session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        userId: user.uid,
                        email: email,
                        name: name,
                        phone: phone,
                        address: address,
                        userType: userType
                    })
                });
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast({
                        title: 'Success',
                        message: 'Account created successfully! Redirecting...',
                        type: 'success',
                        duration: 2000
                    });
                    
                    setTimeout(() => {
                        window.location.href = userType === 'buyer' ? '/buyer/dashboard.php' : '/seller/dashboard.php';
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to create session');
                }
            });
        })
        .catch((error) => {
            console.error("Registration error:", error);
            
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
                    errorMessage = 'Email/password accounts are not enabled. Please contact support.';
                    break;
                default:
                    errorMessage = error.message || errorMessage;
            }
            
            showToast({
                title: 'Registration Failed',
                message: errorMessage,
                type: 'error'
            });
        });
}

function loginUser(email, password, userType) {
    firebase.auth().signInWithEmailAndPassword(email, password)
        .then((userCredential) => {
            const user = userCredential.user;
            return firebase.firestore().collection('users').doc(user.uid).get()
                .then(doc => {
                    if (doc.exists) {
                        const userData = doc.data();
                        
                        if (userData.userType !== userType) {
                            throw new Error(`This account is registered as a ${userData.userType}, not a ${userType}`);
                        }
                        
                        return fetch('/includes/create_session.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                userId: user.uid,
                                email: userData.email,
                                name: userData.name,
                                phone: userData.phone || '',
                                address: userData.address || '',
                                userType: userData.userType
                            })
                        });
                    } else {
                        throw new Error('User profile not found');
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast({
                            title: 'Welcome Back!',
                            message: `Welcome back, ${userData.name}! Redirecting...`,
                            type: 'success',
                            duration: 2000
                        });
                        
                        setTimeout(() => {
                            window.location.href = userType === 'buyer' ? '/buyer/dashboard.php' : '/seller/dashboard.php';
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Failed to create session');
                    }
                });
        })
        .catch((error) => {
            console.error("Login error:", error);
            
            let errorMessage = 'An error occurred during login.';
            
            switch (error.code) {
                case 'auth/user-not-found':
                    errorMessage = 'No account found with this email address. Please check your email or create a new account.';
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
                    errorMessage = error.message || errorMessage;
            }
            
            showToast({
                title: 'Login Failed',
                message: errorMessage,
                type: 'error'
            });
        });
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
                    
                    setTimeout(() => {
                        window.location.href = '/logout.php';
                    }, 2000);
                })
                .catch((error) => {
                    console.error("Logout error:", error);
                    showToast({
                        title: 'Error',
                        message: 'Error logging out. Please try again.',
                        type: 'error'
                    });
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
    
    if (formData.phone && !/^[\+]?[1-9][\d]{0,15}$/.test(formData.phone.replace(/\s/g, ''))) {
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
