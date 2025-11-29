let firebaseConfig = {};
let firebaseInitialized = false;
let firebaseApp = null;

function initializeFirebase(config) {
    return new Promise((resolve, reject) => {
        try {
            if (typeof firebase === 'undefined' || !firebase.apps) {
                reject(new Error('Firebase SDK not loaded'));
                return;
            }

            if (firebase.apps.length === 0) {
                firebaseApp = firebase.initializeApp(config);
            } else {
                firebaseApp = firebase.apps[0];
            }
            
            try {
                if (firebase.firestore) {
                    // Skip settings application to avoid warnings
                    console.log('Firestore initialized without custom settings');
                }
            } catch (e) {
                console.warn('Firestore initialization issue:', e);
                // Continue anyway - not critical
            }
            
            firebaseInitialized = true;
            console.log('✅ Firebase initialized successfully');
            document.dispatchEvent(new Event('firebase-ready'));
            resolve(firebaseApp);
        } catch (error) {
            console.error('Firebase initialization failed:', error);
            reject(error);
        }
    });
}

function loadFirebaseConfig() {
    return new Promise((resolve, reject) => {
        fetch('/includes/get_firebase_config.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(config => {
                resolve(config);
            })
            .catch(error => {
                console.error('Error loading Firebase config from server, using fallback:', error);
                // Fallback to hardcoded config
                resolve({
                    'apiKey': "AIzaSyCIjDPMvgKVTpleUCYWtMIu-K6bW1gHJZY",
                    'authDomain': "greentrade-project.firebaseapp.com",
                    'projectId': "greentrade-project",
                    'storageBucket': "greentrade-project.appspot.com",
                    'messagingSenderId': "582047266659",
                    'appId': "1:582047266659:web:47054d9178fbd66f0d8556",
                    'measurementId': "G-M2FMJ35F4K"
                });
            });
    });
}

// Main initialization
loadFirebaseConfig()
    .then(config => {
        firebaseConfig = config;
        return initializeFirebase(config);
    })
    .catch(error => {
        console.error('Firebase initialization failed:', error);
        document.dispatchEvent(new CustomEvent('firebase-error', { detail: error }));
    });

function waitForFirebase(cb) {
    if (typeof cb !== 'function') {
        console.error('waitForFirebase: callback must be a function');
        return;
    }

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
            firebase.firestore().collection('users').doc(userId).set(userData)
                .then(resolve)
                .catch(reject);
        });
    });
}

// Expose functions globally
if (typeof window !== 'undefined') {
    window.firebaseConfig = firebaseConfig;
    window.waitForFirebase = waitForFirebase;
    window.getCurrentUser = getCurrentUser;
    window.checkAuth = checkAuth;
    window.getUserProfile = getUserProfile;
    window.updateUserProfile = updateUserProfile;
}