<?php
require_once __DIR__ . '/includes/session.php';
include 'includes/auth.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$userType = $_SESSION['user_type'] ?? 'buyer';
$userName = $_SESSION['name'] ?? '';
$userEmail = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Feedback - Green Trade</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Send Feedback</h1>
                <p class="page-subtitle">Help us improve by sharing your thoughts and suggestions</p>
            </div>

            <div class="feedback-container">
                <div class="feedback-card">
                    <div class="feedback-header">
                        <div class="feedback-icon">💬</div>
                        <h2 class="feedback-title">We'd love to hear from you!</h2>
                        <p class="feedback-description">
                            Your feedback helps us make Green Trade better for everyone.
                            Please share your thoughts, suggestions, or report any issues you've encountered.
                        </p>
                    </div>

                    <form id="feedback-form" class="feedback-form">
                        <div class="form-section">
                            <h3 class="section-title">Your Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($userName); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="user-type" class="form-label">Account Type</label>
                                <input type="text" class="form-control" id="user-type" value="<?php echo htmlspecialchars(ucfirst($userType)); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="section-title">Feedback Details</h3>
                            <div class="form-group">
                                <label for="subject" class="form-label">Subject *</label>
                                <select class="form-control" id="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="bug-report">Bug Report</option>
                                    <option value="feature-request">Feature Request</option>
                                    <option value="improvement">Improvement Suggestion</option>
                                    <option value="general-feedback">General Feedback</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category">
                                    <option value="">Select a category (optional)</option>
                                    <option value="user-interface">User Interface</option>
                                    <option value="functionality">Functionality</option>
                                    <option value="performance">Performance</option>
                                    <option value="mobile-app">Mobile Experience</option>
                                    <option value="products">Products</option>
                                    <option value="orders">Orders</option>
                                    <option value="payments">Payments</option>
                                    <option value="customer-service">Customer Service</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-control" id="priority">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" rows="6" placeholder="Please describe your feedback in detail..." required></textarea>
                                <div class="form-help">Be as specific as possible to help us understand and address your feedback.</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Attachments (Optional)</label>
                                <div class="file-upload-area" id="file-upload-area">
                                    <div class="upload-placeholder">
                                        <div class="upload-icon">📎</div>
                                        <div class="upload-text">Click to add screenshots or files</div>
                                        <div class="upload-subtext">Max 5MB per file, supported: JPG, PNG, PDF</div>
                                    </div>
                                    <input type="file" id="attachments" multiple accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                                </div>
                                <div id="file-list" class="file-list"></div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="clearForm()">Clear Form</button>
                            <button type="submit" class="btn btn-primary" id="submit-feedback">
                                <span class="btn-text">Send Feedback</span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Sending...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-storage-compat.js"></script>

    <!-- Core Dependencies -->
    <script src="assets/js/firebase.js"></script>
    <script src="assets/js/dialogs.js"></script>

    <!-- Feedback Service -->
    <script src="assets/js/services/feedbackService.js"></script>

    <style>
        .main-content {
            padding: var(--space-8) 0;
            min-height: calc(100vh - 200px);
        }

        .page-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: var(--space-3);
        }

        .page-subtitle {
            font-size: 1.125rem;
            color: var(--gray-600);
            margin: 0;
        }

        .feedback-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .feedback-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .feedback-header {
            background: linear-gradient(135deg, var(--primary-50), var(--primary-100));
            padding: var(--space-8);
            text-align: center;
            border-bottom: 1px solid var(--gray-200);
        }

        .feedback-icon {
            font-size: 3rem;
            margin-bottom: var(--space-4);
        }

        .feedback-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: var(--space-3);
        }

        .feedback-description {
            color: var(--gray-600);
            line-height: 1.6;
            margin: 0;
            max-width: 600px;
            margin: 0 auto;
        }

        .feedback-form {
            padding: var(--space-8);
        }

        .form-section {
            margin-bottom: var(--space-8);
            padding-bottom: var(--space-6);
            border-bottom: 1px solid var(--gray-100);
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-4);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-4);
            margin-bottom: var(--space-4);
        }

        .form-group {
            margin-bottom: var(--space-4);
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: var(--space-2);
        }

        .form-control {
            width: 100%;
            padding: var(--space-3) var(--space-4);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            transition: all var(--transition-fast);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        .form-control[readonly] {
            background: var(--gray-50);
            cursor: not-allowed;
        }

        .form-help {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: var(--space-2);
        }

        .file-upload-area {
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-lg);
            padding: var(--space-6);
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-fast);
            position: relative;
        }

        .file-upload-area:hover {
            border-color: var(--primary-400);
            background: var(--primary-50);
        }

        .upload-placeholder {
            pointer-events: none;
        }

        .upload-icon {
            font-size: 2rem;
            margin-bottom: var(--space-2);
        }

        .upload-text {
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: var(--space-1);
        }

        .upload-subtext {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .file-list {
            margin-top: var(--space-4);
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-3);
            background: var(--gray-50);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-2);
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .file-name {
            font-weight: 500;
            color: var(--gray-900);
        }

        .file-size {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        .file-remove {
            color: var(--error-500);
            cursor: pointer;
            padding: var(--space-1);
            border-radius: var(--radius-sm);
            transition: all var(--transition-fast);
        }

        .file-remove:hover {
            background: var(--error-50);
        }

        .form-actions {
            display: flex;
            gap: var(--space-3);
            justify-content: flex-end;
            padding-top: var(--space-6);
            border-top: 1px solid var(--gray-200);
        }

        .btn {
            padding: var(--space-3) var(--space-6);
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
        }

        .btn-primary {
            background: var(--primary-500);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-600);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .d-none {
            display: none !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: var(--space-6) 0;
            }

            .page-title {
                font-size: 2rem;
            }

            .feedback-header {
                padding: var(--space-6);
            }

            .feedback-form {
                padding: var(--space-6);
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: var(--space-3);
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        let feedbackService = null;
        let attachedFiles = [];

        document.addEventListener('DOMContentLoaded', function() {
            waitForFirebase(() => {
                initializeFeedback();
            });
        });

        async function initializeFeedback() {
            try {
                feedbackService = new FeedbackService();
                await feedbackService.init();
                setupFileUpload();
            } catch (error) {
                console.error('Error initializing feedback:', error);
                showError('Failed to initialize feedback form. Please refresh the page.');
            }
        }

        function setupFileUpload() {
            const uploadArea = document.getElementById('file-upload-area');
            const fileInput = document.getElementById('attachments');

            uploadArea.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', handleFileSelection);
        }

        function handleFileSelection(event) {
            const files = Array.from(event.target.files);

            // Validate files
            const validFiles = [];
            const errors = [];

            files.forEach(file => {
                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    errors.push(`${file.name}: File size exceeds 5MB limit`);
                    return;
                }

                if (!['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'].includes(file.type)) {
                    errors.push(`${file.name}: Unsupported file type. Only JPG, PNG, and PDF are allowed`);
                    return;
                }

                validFiles.push(file);
            });

            // Show errors if any
            if (errors.length > 0) {
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'File Upload Error',
                        message: errors.join('\n'),
                        type: 'error'
                    });
                }
                return;
            }

            // Add valid files
            validFiles.forEach(file => {
                attachedFiles.push(file);
            });

            updateFileList();
        }

        function updateFileList() {
            const fileList = document.getElementById('file-list');
            fileList.innerHTML = '';

            attachedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';

                const fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';

                fileItem.innerHTML = `
                    <div class="file-info">
                        <span class="file-icon">📎</span>
                        <div>
                            <div class="file-name">${escapeHtml(file.name)}</div>
                            <div class="file-size">${fileSize}</div>
                        </div>
                    </div>
                    <button class="file-remove" onclick="removeFile(${index})" title="Remove file">
                        <i data-feather="x"></i>
                    </button>
                `;

                fileList.appendChild(fileItem);
            });

            // Initialize Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }

        function removeFile(index) {
            attachedFiles.splice(index, 1);
            updateFileList();
        }

        function clearForm() {
            document.getElementById('subject').value = '';
            document.getElementById('category').value = '';
            document.getElementById('priority').value = 'medium';
            document.getElementById('message').value = '';
            attachedFiles = [];
            updateFileList();
        }

        // Form submission
        document.getElementById('feedback-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submit-feedback');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');

            // Validate form
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();

            if (!subject) {
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Validation Error',
                        message: 'Please select a subject.',
                        type: 'warning'
                    });
                }
                return;
            }

            if (!message) {
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Validation Error',
                        message: 'Please enter your feedback message.',
                        type: 'warning'
                    });
                }
                return;
            }

            // Show loading state
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            submitBtn.disabled = true;

            try {
                const feedbackData = {
                    subject: subject,
                    category: document.getElementById('category').value || null,
                    priority: document.getElementById('priority').value,
                    message: message,
                    attachments: attachedFiles
                };

                const result = await feedbackService.submitFeedback(feedbackData);

                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Feedback Sent!',
                        message: 'Thank you for your feedback. We\'ll review it and get back to you if needed.',
                        type: 'success'
                    });
                }

                // Clear form
                clearForm();

            } catch (error) {
                console.error('Error submitting feedback:', error);
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Submission Failed',
                        message: error.message || 'Failed to send feedback. Please try again.',
                        type: 'error'
                    });
                }
            } finally {
                // Reset button state
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
                submitBtn.disabled = false;
            }
        });

        function showError(message) {
            const container = document.querySelector('.feedback-container');
            container.innerHTML = `
                <div class="feedback-card">
                    <div class="feedback-header" style="background: linear-gradient(135deg, #fee2e2, #fecaca);">
                        <div class="feedback-icon">⚠️</div>
                        <h2 class="feedback-title">Error</h2>
                        <p class="feedback-description">${escapeHtml(message)}</p>
                        <button class="btn btn-primary" onclick="location.reload()">Refresh Page</button>
                    </div>
                </div>
            `;
        }

        function escapeHtml(str) {
            if (typeof str !== 'string') return '';
            const map = {
                '&': '&',
                '<': '<',
                '>': '>',
                '"': '"',
                "'": '&#039;'
            };
            return str.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>

</html>