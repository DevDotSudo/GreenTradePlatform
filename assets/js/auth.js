/**
 * Authentication Functions
 * This script handles user authentication functionalities
 */

// Function to register a new user
function registerUser(email, password, name, phone, address, userType) {
    const errorElement = document.getElementById('register-error');
    
    // Clear previous errors
    errorElement.textContent = '';
    errorElement.classList.add('d-none');
    
    // Create user with Firebase Authentication
    firebase.auth().createUserWithEmailAndPassword(email, password)
        .then((userCredential) => {
            // Get user ID
            const user = userCredential.user;
            
            // Create user profile in Firestore
            return firebase.firestore().collection('users').doc(user.uid).set({
                email: email,
                name: name,
                phone: phone,
                address: address,
                userType: userType,
                createdAt: firebase.firestore.FieldValue.serverTimestamp()
            })
            .then(() => {
                // Create session via AJAX
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
                    // Redirect to appropriate dashboard
                    window.location.href = userType === 'buyer' ? '/buyer/dashboard.php' : '/seller/dashboard.php';
                } else {
                    throw new Error(data.message || 'Failed to create session');
                }
            });
        })
        .catch((error) => {
            // Handle errors
            console.error("Registration error:", error);
            errorElement.textContent = error.message;
            errorElement.classList.remove('d-none');
        });
}

// Function to login a user
function loginUser(email, password, userType) {
    const errorElement = document.getElementById('login-error');
    
    // Clear previous errors
    errorElement.textContent = '';
    errorElement.classList.add('d-none');
    
    // Sign in with Firebase Authentication
    firebase.auth().signInWithEmailAndPassword(email, password)
        .then((userCredential) => {
            // Get user ID
            const user = userCredential.user;
            
            // Get user profile from Firestore
            return firebase.firestore().collection('users').doc(user.uid).get()
                .then(doc => {
                    if (doc.exists) {
                        const userData = doc.data();
                        
                        // Check if user type matches
                        if (userData.userType !== userType) {
                            throw new Error(`This account is registered as a ${userData.userType}, not a ${userType}`);
                        }
                        
                        // Create session via AJAX
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
                        // Redirect to appropriate dashboard
                        window.location.href = userType === 'buyer' ? '/buyer/dashboard.php' : '/seller/dashboard.php';
                    } else {
                        throw new Error(data.message || 'Failed to create session');
                    }
                });
        })
        .catch((error) => {
            // Handle errors
            console.error("Login error:", error);
            errorElement.textContent = error.message;
            errorElement.classList.remove('d-none');
        });
}

// Function to log out a user
function logoutUser() {
    firebase.auth().signOut()
        .then(() => {
            // Sign-out successful, redirect to logout page
            window.location.href = '/logout.php';
        })
        .catch((error) => {
            // An error happened
            console.error("Logout error:", error);
            alert('Error logging out. Please try again.');
        });
}

// Helper function to show error messages
function showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.classList.remove('d-none');
    }
}
