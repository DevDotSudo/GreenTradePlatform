function initTooltips() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }
}

function formatCurrency(amount) {
    const num = parseFloat(amount);
    return isNaN(num) ? '₱0.00' : '₱' + num.toFixed(2);
}

function formatDate(timestamp) {
    if (!timestamp) return '';
    const date = timestamp.toDate ? timestamp.toDate() : new Date(timestamp);
    return date.toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'Pending': return 'bg-warning';
        case 'Processing': return 'bg-info';
        case 'Out for Delivery': return 'bg-primary';
        case 'Delivered': return 'bg-success';
        case 'Cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function truncateText(text, maxLength) {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

function getUrlParameter(name) {
    name = name.replace(/[[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

class LoadingOverlay {
    constructor() {
        this.createOverlay();
    }

    createOverlay() {
        if (document.getElementById('loading-overlay')) return;

        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-overlay-content">
                <div class="loading-spinner"></div>
                <p class="loading-text">Loading...</p>
            </div>
        `;
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        `;

        document.body.appendChild(overlay);

        const style = document.createElement('style');
        style.textContent = `
            .loading-overlay-content {
                background: white;
                padding: 2rem;
                border-radius: 8px;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
            .loading-spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #28a745;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 1rem;
            }
            .loading-text {
                margin: 0;
                color: #333;
                font-size: 1.1rem;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }

    show(message = 'Loading...') {
        const overlay = document.getElementById('loading-overlay');
        const text = overlay.querySelector('.loading-text');
        if (text) text.textContent = message;
        overlay.style.display = 'flex';
    }

    hide() {
        const overlay = document.getElementById('loading-overlay');
        overlay.style.display = 'none';
    }
}

if (typeof window !== "undefined") {
    window.loadingOverlay = new LoadingOverlay();
    window.formatCurrency = formatCurrency;
    window.formatDate = formatDate;
    window.getStatusBadgeClass = getStatusBadgeClass;
    window.truncateText = truncateText;
    window.getUrlParameter = getUrlParameter;
}
