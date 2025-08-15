<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green Trade - Modern UI Demo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-brand">
            <div class="container">
                <h1 class="brand-title">
                    <a href="/" class="brand-link">
                        <span class="brand-icon">🌱</span>
                        Green Trade - UI Demo
                    </a>
                </h1>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="demo-section">
                <h1 class="demo-title">Modern UI Components Demo</h1>
                <p class="demo-subtitle">Showcasing the new design system and dialog components</p>
            </div>

            <!-- Buttons Demo -->
            <div class="demo-card">
                <h2 class="demo-card-title">Button Components</h2>
                <div class="demo-buttons">
                    <button class="btn btn-primary">Primary Button</button>
                    <button class="btn btn-secondary">Secondary Button</button>
                    <button class="btn btn-success">Success Button</button>
                    <button class="btn btn-outline-primary">Outline Primary</button>
                    <button class="btn btn-outline-success">Outline Success</button>
                    <button class="btn btn-sm">Small Button</button>
                    <button class="btn btn-lg">Large Button</button>
                </div>
            </div>

            <!-- Cards Demo -->
            <div class="demo-card">
                <h2 class="demo-card-title">Card Components</h2>
                <div class="demo-cards">
                    <div class="card">
                        <div class="card-header">
                            <h3>Sample Card</h3>
                        </div>
                        <div class="card-body">
                            <p>This is a sample card component with modern styling.</p>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary">Action</button>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h3>Simple Card</h3>
                            <p>A card without header and footer sections.</p>
                            <button class="btn btn-outline-primary">Learn More</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Demo -->
            <div class="demo-card">
                <h2 class="demo-card-title">Form Components</h2>
                <form class="demo-form">
                    <div class="form-group">
                        <label for="demo-name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="demo-name" placeholder="Enter your name">
                    </div>
                    
                    <div class="form-group">
                        <label for="demo-email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="demo-email" placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="demo-message" class="form-label">Message</label>
                        <textarea class="form-control" id="demo-message" rows="3" placeholder="Enter your message"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Form</button>
                </form>
            </div>

            <!-- Alert Demo -->
            <div class="demo-card">
                <h2 class="demo-card-title">Alert Components</h2>
                <div class="demo-alerts">
                    <div class="alert alert-success">
                        <strong>Success!</strong> This is a success alert message.
                    </div>
                    <div class="alert alert-warning">
                        <strong>Warning!</strong> This is a warning alert message.
                    </div>
                    <div class="alert alert-error">
                        <strong>Error!</strong> This is an error alert message.
                    </div>
                    <div class="alert alert-info">
                        <strong>Info!</strong> This is an info alert message.
                    </div>
                </div>
            </div>

            <!-- Badge Demo -->
            <div class="demo-card">
                <h2 class="demo-card-title">Badge Components</h2>
                <div class="demo-badges">
                    <span class="badge badge-success">Success</span>
                    <span class="badge badge-warning">Warning</span>
                    <span class="badge badge-error">Error</span>
                    <span class="badge badge-info">Info</span>
                    <span class="badge badge-secondary">Secondary</span>
                </div>
            </div>

            <!-- Dialog Demo -->
            <div class="demo-card">
                <h2 class="demo-card-title">Dialog Components</h2>
                <div class="demo-dialogs">
                    <button class="btn btn-primary" onclick="showSuccessDialog()">Success Dialog</button>
                    <button class="btn btn-warning" onclick="showWarningDialog()">Warning Dialog</button>
                    <button class="btn btn-error" onclick="showErrorDialog()">Error Dialog</button>
                    <button class="btn btn-info" onclick="showInfoDialog()">Info Dialog</button>
                    <button class="btn btn-secondary" onclick="showConfirmDialog()">Confirm Dialog</button>
                </div>
            </div>

            <!-- Toast Demo -->
            <div class="demo-card">
                <h2 class="demo-card-title">Toast Notifications</h2>
                <div class="demo-toasts">
                    <button class="btn btn-success" onclick="showSuccessToast()">Success Toast</button>
                    <button class="btn btn-warning" onclick="showWarningToast()">Warning Toast</button>
                    <button class="btn btn-error" onclick="showErrorToast()">Error Toast</button>
                    <button class="btn btn-info" onclick="showInfoToast()">Info Toast</button>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/dialogs.js"></script>
    
    <style>
    .main-content {
        padding: var(--space-8) 0;
        min-height: calc(100vh - 200px);
    }

    .demo-section {
        text-align: center;
        margin-bottom: var(--space-12);
    }

    .demo-title {
        font-size: 3rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: var(--space-3);
    }

    .demo-subtitle {
        font-size: 1.25rem;
        color: var(--gray-600);
        margin: 0;
    }

    .demo-card {
        background: white;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--gray-200);
        padding: var(--space-8);
        margin-bottom: var(--space-8);
    }

    .demo-card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: var(--space-6);
        border-bottom: 2px solid var(--gray-200);
        padding-bottom: var(--space-4);
    }

    .demo-buttons {
        display: flex;
        gap: var(--space-4);
        flex-wrap: wrap;
        align-items: center;
    }

    .demo-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--space-6);
    }

    .demo-form {
        max-width: 500px;
    }

    .demo-alerts {
        display: flex;
        flex-direction: column;
        gap: var(--space-4);
    }

    .demo-badges {
        display: flex;
        gap: var(--space-3);
        flex-wrap: wrap;
        align-items: center;
    }

    .demo-dialogs {
        display: flex;
        gap: var(--space-4);
        flex-wrap: wrap;
    }

    .demo-toasts {
        display: flex;
        gap: var(--space-4);
        flex-wrap: wrap;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .demo-title {
            font-size: 2rem;
        }

        .demo-subtitle {
            font-size: 1rem;
        }

        .demo-card {
            padding: var(--space-6);
        }

        .demo-buttons,
        .demo-dialogs,
        .demo-toasts {
            flex-direction: column;
            align-items: stretch;
        }

        .demo-buttons .btn,
        .demo-dialogs .btn,
        .demo-toasts .btn {
            width: 100%;
        }
    }
    </style>

    <script>
        // Dialog Demo Functions
        function showSuccessDialog() {
            showAlert({
                title: 'Success!',
                message: 'This is a success dialog. Everything went well!',
                type: 'success',
                buttonText: 'Great!'
            });
        }

        function showWarningDialog() {
            showAlert({
                title: 'Warning!',
                message: 'This is a warning dialog. Please be careful!',
                type: 'warning',
                buttonText: 'I Understand'
            });
        }

        function showErrorDialog() {
            showAlert({
                title: 'Error!',
                message: 'This is an error dialog. Something went wrong!',
                type: 'error',
                buttonText: 'OK'
            });
        }

        function showInfoDialog() {
            showAlert({
                title: 'Information',
                message: 'This is an info dialog. Here is some useful information!',
                type: 'info',
                buttonText: 'Got it!'
            });
        }

        function showConfirmDialog() {
            showConfirm({
                title: 'Confirm Action',
                message: 'Are you sure you want to proceed with this action?',
                type: 'warning',
                confirmText: 'Yes, Proceed',
                cancelText: 'Cancel',
                onConfirm: () => {
                    showToast({
                        title: 'Confirmed',
                        message: 'You confirmed the action!',
                        type: 'success'
                    });
                },
                onCancel: () => {
                    showToast({
                        title: 'Cancelled',
                        message: 'Action was cancelled.',
                        type: 'info'
                    });
                }
            });
        }

        // Toast Demo Functions
        function showSuccessToast() {
            showToast({
                title: 'Success!',
                message: 'This is a success toast notification.',
                type: 'success'
            });
        }

        function showWarningToast() {
            showToast({
                title: 'Warning!',
                message: 'This is a warning toast notification.',
                type: 'warning'
            });
        }

        function showErrorToast() {
            showToast({
                title: 'Error!',
                message: 'This is an error toast notification.',
                type: 'error'
            });
        }

        function showInfoToast() {
            showToast({
                title: 'Info',
                message: 'This is an info toast notification.',
                type: 'info'
            });
        }

        // Form Demo
        document.querySelector('.demo-form').addEventListener('submit', function(e) {
            e.preventDefault();
            showToast({
                title: 'Form Submitted',
                message: 'Thank you for submitting the form!',
                type: 'success'
            });
        });
    </script>
</body>
</html>
