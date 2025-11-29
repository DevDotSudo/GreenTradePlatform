/**
 * Feedback Service - Handles user feedback submission
 * Provides functionality to submit feedback with attachments to Firebase
 */
class FeedbackService {
    constructor() {
        this.db = null;
        this.storage = null;
        this.currentUser = null;
    }

    /**
     * Initialize the feedback service
     */
    async init() {
        if (this.db) return; // Already initialized

        try {
            // Wait for Firebase to be ready
            if (typeof waitForFirebase === 'function') {
                await new Promise(resolve => waitForFirebase(() => resolve()));
            }

            if (!firebase || !firebase.firestore || !firebase.storage) {
                throw new Error('Firebase Firestore and Storage not available');
            }

            this.db = firebase.firestore();
            this.storage = firebase.storage();

            // Get current user
            this.currentUser = await this.getCurrentUser();
            if (!this.currentUser) {
                throw new Error('User not authenticated');
            }

        } catch (error) {
            console.error('FeedbackService initialization failed:', error);
            throw error;
        }
    }

    /**
     * Get current authenticated user
     */
    async getCurrentUser() {
        if (typeof getCurrentUser === 'function') {
            return await getCurrentUser();
        }
        return firebase.auth().currentUser;
    }

    /**
     * Submit feedback with optional attachments
     */
    async submitFeedback(feedbackData) {
        await this.init();

        try {
            const { subject, category, priority, message, attachments = [] } = feedbackData;

            // Validate required fields
            if (!subject || !message) {
                throw new Error('Subject and message are required');
            }

            // Upload attachments if any
            const attachmentUrls = [];
            if (attachments.length > 0) {
                for (const file of attachments) {
                    const downloadURL = await this.uploadAttachment(file);
                    attachmentUrls.push({
                        name: file.name,
                        url: downloadURL,
                        size: file.size,
                        type: file.type
                    });
                }
            }

            // Create feedback document
            const feedbackDoc = {
                userId: this.currentUser.uid,
                userName: this.currentUser.displayName || 'Anonymous',
                userEmail: this.currentUser.email,
                userType: this.getUserType(),
                subject: subject,
                category: category || null,
                priority: priority || 'medium',
                message: message.trim(),
                attachments: attachmentUrls,
                status: 'pending', // pending, in-review, resolved, closed
                adminResponse: null,
                adminResponseDate: null,
                submittedAt: firebase.firestore.FieldValue.serverTimestamp(),
                updatedAt: firebase.firestore.FieldValue.serverTimestamp(),
                resolvedAt: null
            };

            // Add to Firestore
            const docRef = await this.db.collection('feedback').add(feedbackDoc);

            return {
                id: docRef.id,
                ...feedbackDoc
            };

        } catch (error) {
            console.error('Error submitting feedback:', error);
            throw error;
        }
    }

    /**
     * Upload attachment to Firebase Storage
     */
    async uploadAttachment(file) {
        try {
            // Create unique filename
            const timestamp = Date.now();
            const randomId = Math.random().toString(36).substring(2, 15);
            const fileExtension = file.name.split('.').pop();
            const fileName = `feedback/${this.currentUser.uid}/${timestamp}_${randomId}.${fileExtension}`;

            // Upload file
            const storageRef = this.storage.ref(fileName);
            const snapshot = await storageRef.put(file);

            // Get download URL
            const downloadURL = await snapshot.ref.getDownloadURL();

            return downloadURL;

        } catch (error) {
            console.error('Error uploading attachment:', error);
            throw new Error(`Failed to upload ${file.name}: ${error.message}`);
        }
    }

    /**
     * Get user's feedback history
     */
    async getUserFeedback(limit = 20) {
        await this.init();

        try {
            const snapshot = await this.db.collection('feedback')
                .where('userId', '==', this.currentUser.uid)
                .orderBy('submittedAt', 'desc')
                .limit(limit)
                .get();

            return snapshot.docs.map(doc => ({
                id: doc.id,
                ...doc.data(),
                submittedAt: doc.data().submittedAt?.toDate?.() || new Date(doc.data().submittedAt)
            }));

        } catch (error) {
            console.error('Error getting user feedback:', error);
            throw error;
        }
    }

    /**
     * Get feedback by ID
     */
    async getFeedbackById(feedbackId) {
        await this.init();

        try {
            const doc = await this.db.collection('feedback').doc(feedbackId).get();

            if (!doc.exists) {
                throw new Error('Feedback not found');
            }

            const data = doc.data();

            // Check if user owns this feedback
            if (data.userId !== this.currentUser.uid) {
                throw new Error('Unauthorized to view this feedback');
            }

            return {
                id: doc.id,
                ...data,
                submittedAt: data.submittedAt?.toDate?.() || new Date(data.submittedAt)
            };

        } catch (error) {
            console.error('Error getting feedback by ID:', error);
            throw error;
        }
    }

    /**
     * Get user type from session (fallback method)
     */
    getUserType() {
        // Try to get from session if available
        if (typeof window !== 'undefined' && window.sessionStorage) {
            return window.sessionStorage.getItem('userType') || 'buyer';
        }
        return 'buyer'; // Default fallback
    }

    /**
     * Get feedback statistics for user
     */
    async getFeedbackStats() {
        await this.init();

        try {
            const feedback = await this.getUserFeedback(100); // Get more to calculate stats

            const stats = {
                total: feedback.length,
                pending: feedback.filter(f => f.status === 'pending').length,
                inReview: feedback.filter(f => f.status === 'in-review').length,
                resolved: feedback.filter(f => f.status === 'resolved').length,
                closed: feedback.filter(f => f.status === 'closed').length
            };

            return stats;

        } catch (error) {
            console.error('Error getting feedback stats:', error);
            return { total: 0, pending: 0, inReview: 0, resolved: 0, closed: 0 };
        }
    }
}

// Export for use in other modules
window.FeedbackService = FeedbackService;