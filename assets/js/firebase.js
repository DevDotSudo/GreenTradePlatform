let firebaseConfig = {};
let firebaseInitialized = false;
let firebaseApp = null;

fetch('/includes/get_firebase_config.php')
    .then(response => response.json())
    .then(config => {
        firebaseConfig = config;
        if (typeof firebase !== 'undefined' && firebase.apps.length === 0) {
            firebaseApp = firebase.initializeApp(firebaseConfig);
            firebaseInitialized = true;
            console.log('✅ Firebase initialized successfully');
            document.dispatchEvent(new Event('firebase-ready'));
        } else if (firebase.apps.length > 0) {
            firebaseApp = firebase.apps[0];
            firebaseInitialized = true;
            console.log('ℹ️ Firebase already initialized');
            document.dispatchEvent(new Event('firebase-ready'));
        } else {
            console.error('❌ Firebase SDK not loaded');
        }
    })
    .catch(error => {
        console.error('Error loading Firebase config:', error);
    });

function waitForFirebase(cb) {
    if (firebaseInitialized) {
        cb();
    } else {
        document.addEventListener('firebase-ready', cb);
    }
}

function getCurrentUser() {
    return new Promise((resolve, reject) => {
        waitForFirebase(() => {
            const unsubscribe = firebase.auth().onAuthStateChanged(user => {
                unsubscribe();
                resolve(user);
            }, reject);
        });
    });
}

function checkAuth() {
    return new Promise((resolve, reject) => {
        waitForFirebase(() => {
            firebase.auth().onAuthStateChanged(user => {
                if (user) {
                    resolve(user);
                } else {
                    reject(new Error('User not authenticated'));
                }
            });
        });
    });
}

function getUserProfile(userId) {
    return new Promise((resolve, reject) => {
        waitForFirebase(() => {
            firebase.firestore().collection('users').doc(userId).get()
                .then(doc => {
                    if (doc.exists) {
                        resolve(doc.data());
                    } else {
                        reject(new Error('User profile not found'));
                    }
                })
                .catch(reject);
        });
    });
}

function updateUserProfile(userId, userData) {
    return new Promise((resolve, reject) => {
        waitForFirebase(() => {
            firebase.firestore().collection('users').doc(userId).set(userData, { merge: true })
                .then(resolve)
                .catch(reject);
        });
    });
}