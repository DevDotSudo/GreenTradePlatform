(function() {
    if (typeof window !== 'undefined' && window.DialogSystem) {
        console.log('DialogSystem already loaded');
        return;
    }

    class DialogSystem {
        constructor() {
            this.init();
        }

        init() {
            if (typeof document === 'undefined' || !document.body) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => this.init());
                    return;
                }
            }
            if (!document.getElementById('toast-container')) {
                const toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'toast-container';
                if (document.body) {
                    document.body.appendChild(toastContainer);
                }
            }
        }

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

        prompt(options = {}) {
            const {
                title = 'Input',
                message = 'Please enter a value',
                type = 'info',
                placeholder = '',
                defaultValue = ''
            } = options;

            return new Promise((resolve) => {
                const modal = this.createModal();
                const dialog = this.createPromptDialog({
                    title,
                    message,
                    type,
                    placeholder,
                    defaultValue,
                    onConfirm: (value) => {
                        this.closeModal(modal);
                        resolve(value);
                    },
                    onCancel: () => {
                        this.closeModal(modal);
                        resolve(null);
                    }
                });

                modal.appendChild(dialog);
                document.body.appendChild(modal);
                this.showModal(modal);
                setTimeout(() => {
                    const input = modal.querySelector('input[type="number"], input[type="text"]');
                    if (input) input.focus();
                }, 150);
            });
        }

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

            if (duration > 0) {
                setTimeout(() => {
                    this.hideToast(toast);
                }, duration);
            }

            return toast;
        }

        createModal() {
            const modal = document.createElement('div');
            modal.className = 'modal';
            
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop';
            backdrop.addEventListener('click', () => this.closeModal(modal));
            
            modal.appendChild(backdrop);
            return modal;
        }

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

            dialog.querySelector('[data-action="confirm"]').addEventListener('click', onConfirm);
            dialog.querySelector('[data-action="cancel"]').addEventListener('click', onCancel);
            dialog.querySelector('.modal-close').addEventListener('click', onCancel);

            return dialog;
        }

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

            dialog.querySelector('[data-action="close"]').addEventListener('click', onClose);
            dialog.querySelector('.modal-close').addEventListener('click', onClose);

            return dialog;
        }

        createPromptDialog(options) {
            const {
                title,
                message,
                type,
                placeholder,
                defaultValue,
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
                    <div style="margin-top:12px;"><input type="number" min="1" class="prompt-input form-control" placeholder="${placeholder}" value="${defaultValue}"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-action="cancel">Cancel</button>
                    <button type="button" class="btn btn-${this.getButtonType(type)}" data-action="confirm">Add</button>
                </div>
            `;

            dialog.querySelector('[data-action="confirm"]').addEventListener('click', () => {
                const input = dialog.querySelector('.prompt-input');
                const val = input ? input.value.trim() : null;
                if (val !== null && val !== '') {
                    const numVal = parseInt(val, 10);
                    if (!isNaN(numVal) && numVal >= 1) {
                        onConfirm(numVal);
                    } else {
                        const errorMsg = input.parentNode.querySelector('.input-error');
                        if (!errorMsg) {
                            const error = document.createElement('div');
                            error.className = 'input-error';
                            error.style.color = 'red';
                            error.style.fontSize = '0.875rem';
                            error.style.marginTop = '4px';
                            error.textContent = 'Please enter a valid quantity (minimum 1)';
                            input.parentNode.appendChild(error);
                            setTimeout(() => error.remove(), 3000);
                        }
                        input.focus();
                        return;
                    }
                }
                onConfirm(val);
            });
            dialog.querySelector('[data-action="cancel"]').addEventListener('click', onCancel);
            dialog.querySelector('.modal-close').addEventListener('click', onCancel);

            const promptInput = dialog.querySelector('.prompt-input');
            if (promptInput) {
                promptInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        dialog.querySelector('[data-action="confirm"]').click();
                    }
                });
            }

            return dialog;
        }

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

            toast.querySelector('.toast-close').addEventListener('click', () => {
                this.hideToast(toast);
            });

            return toast;
        }

        showModal(modal) {
            document.body.style.overflow = 'hidden';
            modal.offsetHeight;
            modal.classList.add('show');
            setTimeout(() => {
                const firstButton = modal.querySelector('button');
                if (firstButton) firstButton.focus();
            }, 100);
        }

        closeModal(modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
            setTimeout(() => {
                if (modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
            }, 300);
        }

        hideToast(toast) {
            toast.style.animation = 'toastSlideOut 0.3s ease-in forwards';
            
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }

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

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        }

        .toast {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-left: 4px solid;
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 300px;
            max-width: 400px;
            animation: toastSlideIn 0.3s ease-out;
        }

        .toast.toast-success {
            border-left-color: var(--primary-500);
        }

        .toast.toast-warning {
            border-left-color: var(--warning-500);
        }

        .toast.toast-error {
            border-left-color: var(--error-500);
        }

        .toast.toast-info {
            border-left-color: var(--accent-500);
        }

        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .toast-title {
            font-weight: 600;
            margin: 0;
            color: #1f2937;
        }

        .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #6b7280;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast-close:hover {
            color: #374151;
        }

        .toast-message {
            color: #4b5563;
            margin: 4px 0 0 0;
            line-height: 1.5;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .modal-dialog {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            z-index: 10001;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-dialog {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid #e5e7eb;
        }

        .confirm-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .confirm-icon.success { color: #10b981; }
        .confirm-icon.warning { color: #f59e0b; }
        .confirm-icon.error { color: #ef4444; }
        .confirm-icon.info { color: #3b82f6; }

        .confirm-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1f2937;
        }

        .confirm-message {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: 8px;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
            border-color: #6b7280;
        }

        .btn-secondary:hover {
            background: #4b5563;
            border-color: #4b5563;
        }

        .btn-success {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }

        .btn-success:hover {
            background: #059669;
            border-color: #059669;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }

        .btn-warning:hover {
            background: #d97706;
            border-color: #d97706;
        }

        .btn-error {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        .btn-error:hover {
            background: #dc2626;
            border-color: #dc2626;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .prompt-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .prompt-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    `;
    if (document.head) {
        document.head.appendChild(style);
    }

    function initDialogs() {
        if (typeof window.dialogs === 'undefined') {
            window.dialogs = new DialogSystem();
            window.showConfirm = (options) => window.dialogs.confirm(options);
            window.showAlert = (options) => window.dialogs.alert(options);
            window.showToast = (options) => window.dialogs.toast(options);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDialogs);
    } else {
        initDialogs();
    }

    window.DialogSystem = DialogSystem;
})();
