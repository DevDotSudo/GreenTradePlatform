/**
 * Firebase Configuration and Initialization
 * This script initializes Firebase for the application
 */

// Fetch Firebase config from server-side
let firebaseConfig = {};

// Use AJAX to get the Firebase config from PHP
fetch('/includes/get_firebase_config.php')
    .then(response => response.json())
    .then(config => {
        firebaseConfig = config;
        // Initialize Firebase after config is retrieved
        if (typeof firebase !== 'undefined') {
            firebase.initializeApp(firebaseConfig);
        } else {
            console.error('Firebase SDK not loaded');
        }
    })
    .catch(error => {
        console.error('Error loading Firebase config:', error);
    });

// Already initializing Firebase in the fetch callback above
// Keeping these functions accessible globally
} else {
    console.error('Firebase SDK not loaded');
}

// Get the current logged in user
function getCurrentUser() {
    return new Promise((resolve, reject) => {
        const unsubscribe = firebase.auth().onAuthStateChanged(user => {
            unsubscribe();
            resolve(user);
        }, reject);
    });
}

// Check if user is authenticated
function checkAuth() {
    return new Promise((resolve, reject) => {
        firebase.auth().onAuthStateChanged(user => {
            if (user) {
                // User is signed in
                resolve(user);
            } else {
                // No user is signed in
                reject(new Error('User not authenticated'));
            }
        });
    });
}

// Get user profile from Firestore
function getUserProfile(userId) {
    return firebase.firestore().collection('users').doc(userId).get()
        .then(doc => {
            if (doc.exists) {
                return doc.data();
            } else {
                throw new Error('User profile not found');
            }
        });
}

// Create or update user profile in Firestore
function updateUserProfile(userId, userData) {
    return firebase.firestore().collection('users').doc(userId).set(userData, { merge: true });
}
