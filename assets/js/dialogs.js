/**
 * Modern Dialog and Notification System
 * Provides confirmation dialogs and toast notifications
 */

class DialogSystem {
    constructor() {
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toast-container')) {
            const toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }
    }

    /**
     * Show a confirmation dialog
     * @param {Object} options - Dialog options
     * @param {string} options.title - Dialog title
     * @param {string} options.message - Dialog message
     * @param {string} options.type - Dialog type (success, warning, error)
     * @param {string} options.confirmText - Confirm button text
     * @param {string} options.cancelText - Cancel button text
     * @param {Function} options.onConfirm - Callback when confirmed
     * @param {Function} options.onCancel - Callback when cancelled
     */
    confirm(options = {}) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure you want to proceed?',
            type = 'warning',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            onConfirm = () => {},
            onCancel = () => {}
        } = options;

        return new Promise((resolve) => {
            const modal = this.createModal();
            const dialog = this.createConfirmDialog({
                title,
                message,
                type,
                confirmText,
                cancelText,
                onConfirm: () => {
                    this.closeModal(modal);
                    onConfirm();
                    resolve(true);
                },
                onCancel: () => {
                    this.closeModal(modal);
                    onCancel();
                    resolve(false);
                }
            });

            modal.appendChild(dialog);
            document.body.appendChild(modal);
            this.showModal(modal);
        });
    }

    /**
     * Show a simple alert dialog
     * @param {Object} options - Alert options
     * @param {string} options.title - Alert title
     * @param {string} options.message - Alert message
     * @param {string} options.type - Alert type (success, warning, error, info)
     * @param {string} options.buttonText - Button text
     */
    alert(options = {}) {
        const {
            title = 'Alert',
            message = 'This is an alert message.',
            type = 'info',
            buttonText = 'OK'
        } = options;

        return new Promise((resolve) => {
            const modal = this.createModal();
            const dialog = this.createAlertDialog({
                title,
                message,
                type,
                buttonText,
                onClose: () => {
                    this.closeModal(modal);
                    resolve();
                }
            });

            modal.appendChild(dialog);
            document.body.appendChild(modal);
            this.showModal(modal);
        });
    }

    /**
     * Show a toast notification
     * @param {Object} options - Toast options
     * @param {string} options.title - Toast title
     * @param {string} options.message - Toast message
     * @param {string} options.type - Toast type (success, warning, error, info)
     * @param {number} options.duration - Duration in milliseconds (0 = auto-hide disabled)
     */
    toast(options = {}) {
        const {
            title = 'Notification',
            message = 'This is a notification message.',
            type = 'info',
            duration = 5000
        } = options;

        const toast = this.createToast({ title, message, type });
        const container = document.getElementById('toast-container');
        container.appendChild(toast);

        // Auto-hide after duration
        if (duration > 0) {
            setTimeout(() => {
                this.hideToast(toast);
            }, duration);
        }

        return toast;
    }

    /**
     * Create modal backdrop and container
     */
    createModal() {
        const modal = document.createElement('div');
        modal.className = 'modal';
        
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.addEventListener('click', () => this.closeModal(modal));
        
        modal.appendChild(backdrop);
        return modal;
    }

    /**
     * Create confirmation dialog
     */
    createConfirmDialog(options) {
        const {
            title,
            message,
            type,
            confirmText,
            cancelText,
            onConfirm,
            onCancel
        } = options;

        const dialog = document.createElement('div');
        dialog.className = 'modal-dialog confirm-dialog';
        
        const iconMap = {
            success: '✓',
            warning: '⚠',
            error: '✕',
            info: 'ℹ'
        };

        dialog.innerHTML = `
            <div class="modal-header">
                <h5 class="modal-title">${title}</h5>
                <button type="button" class="modal-close" aria-label="Close">×</button>
            </div>
            <div class="modal-body">
                <div class="confirm-icon ${type}">${iconMap[type] || iconMap.info}</div>
                <h3 class="confirm-title">${title}</h3>
                <p class="confirm-message">${message}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-action="cancel">${cancelText}</button>
                <button type="button" class="btn btn-${this.getButtonType(type)}" data-action="confirm">${confirmText}</button>
            </div>
        `;

        // Event listeners
        dialog.querySelector('[data-action="confirm"]').addEventListener('click', onConfirm);
        dialog.querySelector('[data-action="cancel"]').addEventListener('click', onCancel);
        dialog.querySelector('.modal-close').addEventListener('click', onCancel);

        return dialog;
    }

    /**
     * Create alert dialog
     */
    createAlertDialog(options) {
        const {
            title,
            message,
            type,
            buttonText,
            onClose
        } = options;

        const dialog = document.createElement('div');
        dialog.className = 'modal-dialog confirm-dialog';
        
        const iconMap = {
            success: '✓',
            warning: '⚠',
            error: '✕',
            info: 'ℹ'
        };

        dialog.innerHTML = `
            <div class="modal-header">
                <h5 class="modal-title">${title}</h5>
                <button type="button" class="modal-close" aria-label="Close">×</button>
            </div>
            <div class="modal-body">
                <div class="confirm-icon ${type}">${iconMap[type] || iconMap.info}</div>
                <h3 class="confirm-title">${title}</h3>
                <p class="confirm-message">${message}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-${this.getButtonType(type)}" data-action="close">${buttonText}</button>
            </div>
        `;

        // Event listeners
        dialog.querySelector('[data-action="close"]').addEventListener('click', onClose);
        dialog.querySelector('.modal-close').addEventListener('click', onClose);

        return dialog;
    }

    /**
     * Create toast notification
     */
    createToast(options) {
        const {
            title,
            message,
            type
        } = options;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        toast.innerHTML = `
            <div class="toast-header">
                <span class="toast-title">${title}</span>
                <button type="button" class="toast-close" aria-label="Close">×</button>
            </div>
            <div class="toast-message">${message}</div>
        `;

        // Close button event
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.hideToast(toast);
        });

        return toast;
    }

    /**
     * Show modal
     */
    showModal(modal) {
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Trigger reflow
        modal.offsetHeight;
        
        // Show modal
        modal.classList.add('show');
        
        // Focus first button
        setTimeout(() => {
            const firstButton = modal.querySelector('button');
            if (firstButton) firstButton.focus();
        }, 100);
    }

    /**
     * Close modal
     */
    closeModal(modal) {
        modal.classList.remove('show');
        
        // Re-enable body scroll
        document.body.style.overflow = '';
        
        // Remove from DOM after animation
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }

    /**
     * Hide toast with animation
     */
    hideToast(toast) {
        toast.style.animation = 'toastSlideOut 0.3s ease-in forwards';
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    /**
     * Get button type based on dialog type
     */
    getButtonType(type) {
        const typeMap = {
            success: 'success',
            warning: 'warning',
            error: 'error',
            info: 'primary'
        };
        return typeMap[type] || 'primary';
    }
}

// Add slide out animation for toasts
const style = document.createElement('style');
style.textContent = `
    @keyframes toastSlideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
`;
document.head.appendChild(style);

// Create global instance
window.dialogs = new DialogSystem();

// Convenience functions
window.showConfirm = (options) => window.dialogs.confirm(options);
window.showAlert = (options) => window.dialogs.alert(options);
window.showToast = (options) => window.dialogs.toast(options);

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DialogSystem;
}
