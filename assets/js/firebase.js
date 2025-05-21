/**
 * Firebase Configuration and Initialization
 * This script initializes Firebase for the application
 */

// Firebase configuration - retrieved from PHP
const firebaseConfig = {
    apiKey: "AIzaSyCIjDPMvgKVTpleUCYWtMIu-K6bW1gHJZY",
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
