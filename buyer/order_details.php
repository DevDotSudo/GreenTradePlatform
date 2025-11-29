<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';
include '../includes/functions.php';

ensureUserLoggedIn('buyer');

$orderId = isset($_GET['id']) ? trim($_GET['id']) : '';
if (empty($orderId)) {
    header('Location: orders.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Green Trade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <div class="page-header-content">
                    <a href="orders.php" class="back-link">
                        <i data-feather="arrow-left"></i> Back to Orders
                    </a>
                    <h1 class="page-title">Order Details</h1>
                    <p class="page-subtitle">Order #<span id="order-id-display"><?php echo htmlspecialchars(substr($orderId, 0, 8)); ?></span></p>
                </div>
            </div>

            <!-- Order Details Container -->
            <div id="order-details-container">
                <!-- Order details will be loaded here -->
                <div class="loading-state">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Loading order details...</div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>

    <!-- Core Dependencies -->
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/dialogs.js"></script>

    <!-- Services -->
    <script src="../assets/js/services/orderService.js"></script>

    <style>
        .main-content {
            padding: var(--space-8) 0;
            min-height: calc(100vh - 200px);
        }

        .page-header {
            margin-bottom: var(--space-8);
        }

        .page-header-content {
            text-align: center;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            color: var(--primary-600);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: var(--space-4);
            transition: color var(--transition-fast);
        }

        .back-link:hover {
            color: var(--primary-700);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: var(--space-2);
        }

        .page-subtitle {
            font-size: 1.125rem;
            color: var(--gray-600);
            margin: 0;
        }

        .order-details-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            margin-bottom: var(--space-6);
        }

        .order-header {
            background: var(--gray-50);
            padding: var(--space-6);
            border-bottom: 1px solid var(--gray-200);
        }

        .order-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-3);
        }

        .order-id {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .order-date {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .order-status {
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-warning { background: var(--warning-100); color: var(--warning-800); }
        .badge-info { background: var(--info-100); color: var(--info-800); }
        .badge-primary { background: var(--primary-100); color: var(--primary-800); }
        .badge-success { background: var(--success-100); color: var(--success-800); }
        .badge-error { background: var(--error-100); color: var(--error-800); }

        .order-body {
            padding: var(--space-6);
        }

        .order-section {
            margin-bottom: var(--space-6);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-4);
        }

        .shipping-info, .billing-info {
            background: var(--gray-50);
            padding: var(--space-4);
            border-radius: var(--radius-lg);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-2);
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 500;
            color: var(--gray-700);
        }

        .info-value {
            color: var(--gray-900);
        }

        .seller-group {
            margin-bottom: var(--space-6);
            padding-bottom: var(--space-4);
            border-bottom: 1px solid var(--gray-200);
        }

        .seller-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .seller-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-4);
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            padding: var(--space-4);
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-3);
        }

        .item-image {
            width: 80px;
            height: 80px;
            background: var(--gray-200);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-1);
        }

        .item-meta {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .item-price {
            font-weight: 600;
            color: var(--primary-600);
            font-size: 1.125rem;
        }

        .order-summary {
            background: var(--gray-50);
            padding: var(--space-4);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-6);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-2);
        }

        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: var(--space-2);
            border-top: 1px solid var(--gray-200);
            font-weight: 600;
            color: var(--gray-900);
            font-size: 1.125rem;
        }

        .order-actions {
            display: flex;
            gap: var(--space-3);
            flex-wrap: wrap;
            justify-content: center;
        }

        .error-state {
            text-align: center;
            padding: var(--space-12) var(--space-6);
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: var(--space-4);
            opacity: 0.5;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-2);
        }

        .error-description {
            color: var(--gray-600);
            margin-bottom: var(--space-6);
        }

        .loading-state {
            text-align: center;
            padding: var(--space-12) var(--space-6);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--gray-200);
            border-top-color: var(--primary-500);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto var(--space-4);
        }

        .loading-text {
            color: var(--gray-600);
            font-size: 1rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: var(--space-6) 0;
            }

            .page-title {
                font-size: 2rem;
            }

            .order-title {
                flex-direction: column;
                gap: var(--space-2);
                align-items: flex-start;
            }

            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--space-3);
            }

            .item-image {
                width: 60px;
                height: 60px;
            }

            .info-row {
                flex-direction: column;
                gap: var(--space-1);
            }

            .order-actions {
                flex-direction: column;
            }

            .order-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        let orderService = null;
        const orderId = '<?php echo htmlspecialchars($orderId); ?>';

        document.addEventListener('DOMContentLoaded', function() {
            waitForFirebase(() => {
                initializeOrderDetails();
            });
        });

        async function initializeOrderDetails() {
            try {
                orderService = new OrderService();
                await orderService.init();
                await loadOrderDetails();
            } catch (error) {
                console.error('Error initializing order details:', error);
                showError('Failed to load order details. Please refresh the page.');
            }
        }

        async function loadOrderDetails() {
            const container = document.getElementById('order-details-container');

            try {
                container.innerHTML = `
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <div class="loading-text">Loading order details...</div>
                    </div>
                `;

                const order = await orderService.getOrderById(orderId);

                renderOrderDetails(order);

            } catch (error) {
                console.error('Error loading order details:', error);
                if (error.message && error.message.includes('not found')) {
                    showError('Order not found. It may have been deleted or you may not have permission to view it.');
                } else if (error.message && error.message.includes('Unauthorized')) {
                    showError('You do not have permission to view this order.');
                } else {
                    showError('Failed to load order details. Please try again.');
                }
            }
        }

        function renderOrderDetails(order) {
            const container = document.getElementById('order-details-container');
            const statusInfo = orderService.getStatusDisplayInfo(order.status);
            const orderDate = orderService.formatDateTime(order.createdAt || new Date());

            // Update page title
            document.getElementById('order-id-display').textContent = order.id.substring(0, 8);

            // Group items by seller
            const itemsBySeller = {};
            order.items.forEach(item => {
                const sellerId = item.sellerId || 'unknown';
                if (!itemsBySeller[sellerId]) {
                    itemsBySeller[sellerId] = {
                        sellerName: item.sellerName || 'Unknown Seller',
                        items: []
                    };
                }
                itemsBySeller[sellerId].items.push(item);
            });

            let itemsHtml = '';
            Object.keys(itemsBySeller).forEach(sellerId => {
                const sellerGroup = itemsBySeller[sellerId];
                itemsHtml += `<div class="seller-group">
                    <h4 class="seller-name">From: ${escapeHtml(sellerGroup.sellerName)}</h4>`;

                sellerGroup.items.forEach(item => {
                    const itemTotal = (Number(item.price) * Number(item.quantity)).toFixed(2);
                    itemsHtml += `
                        <div class="order-item">
                            <div class="item-image">
                                ${item.imageUrl || item.imageData ?
                                    `<img src="${item.imageUrl || item.imageData}" alt="${item.productName || item.name}">` :
                                    '<span>📦</span>'}
                            </div>
                            <div class="item-details">
                                <div class="item-name">${escapeHtml(item.productName || item.name || 'Unnamed Product')}</div>
                                <div class="item-meta">₱${Number(item.price).toFixed(2)} × ${item.quantity}</div>
                            </div>
                            <div class="item-price">₱${itemTotal}</div>
                        </div>`;
                });

                itemsHtml += '</div>';
            });

            const subtotal = Number(order.subtotal || 0);
            const deliveryFee = Number(order.deliveryFee || 50);
            const total = Number(order.totalAmount || (subtotal + deliveryFee));

            container.innerHTML = `
                <div class="order-details-card">
                    <div class="order-header">
                        <div class="order-title">
                            <div>
                                <div class="order-id">Order #${order.id.substring(0, 8)}</div>
                                <div class="order-date">Placed on ${orderDate}</div>
                            </div>
                            <span class="order-status ${statusInfo.class}">${statusInfo.label}</span>
                        </div>
                    </div>

                    <div class="order-body">
                        <div class="order-section">
                            <h3 class="section-title">Shipping Information</h3>
                            <div class="shipping-info">
                                <div class="info-row">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value">${escapeHtml(order.shippingInfo?.name || 'N/A')}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Phone:</span>
                                    <span class="info-value">${escapeHtml(order.shippingInfo?.phone || 'N/A')}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Address:</span>
                                    <span class="info-value">${escapeHtml(order.shippingInfo?.address || 'N/A')}</span>
                                </div>
                                ${order.shippingInfo?.notes ? `
                                    <div class="info-row">
                                        <span class="info-label">Notes:</span>
                                        <span class="info-value">${escapeHtml(order.notes)}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>

                        <div class="order-section">
                            <h3 class="section-title">Order Items</h3>
                            ${itemsHtml}
                        </div>

                        <div class="order-section">
                            <h3 class="section-title">Order Summary</h3>
                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <span>₱${subtotal.toFixed(2)}</span>
                                </div>
                                <div class="summary-row">
                                    <span>Delivery Fee</span>
                                    <span>₱${deliveryFee.toFixed(2)}</span>
                                </div>
                                <div class="summary-row">
                                    <strong>Total Amount</strong>
                                    <strong>₱${total.toFixed(2)}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="order-actions">
                            ${getOrderActions(order)}
                        </div>
                    </div>
                </div>
            `;

            // Initialize Feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }

        function getOrderActions(order) {
            let actions = '';

            if (order.status === 'Pending') {
                actions += `<button class="btn btn-outline-error cancel-order" data-order-id="${order.id}">
                    Cancel Order
                </button>`;
            }

            actions += `<a href="orders.php" class="btn btn-outline-primary">
                Back to Orders
            </a>`;

            return actions;
        }

        function showError(message) {
            const container = document.getElementById('order-details-container');
            container.innerHTML = `
                <div class="error-state">
                    <div class="error-icon">⚠️</div>
                    <h3 class="error-title">Error</h3>
                    <p class="error-description">${escapeHtml(message)}</p>
                    <div>
                        <a href="orders.php" class="btn btn-primary">Back to Orders</a>
                        <button class="btn btn-outline-primary ms-2" onclick="loadOrderDetails()">Try Again</button>
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

        // Event delegation for dynamic elements
        document.addEventListener('click', async function(e) {
            if (e.target.classList.contains('cancel-order')) {
                const orderId = e.target.dataset.orderId;
                await handleCancelOrder(orderId, e.target);
            }
        });

        async function handleCancelOrder(orderId, button) {
            const confirmed = await showConfirm({
                title: 'Cancel Order',
                message: 'Are you sure you want to cancel this order? This action cannot be undone.',
                type: 'warning',
                confirmText: 'Cancel Order',
                cancelText: 'Keep Order'
            });
            if (!confirmed) {
                return;
            }

            try {
                button.disabled = true;
                button.textContent = 'Cancelling...';

                await orderService.cancelOrder(orderId);

                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Order Cancelled',
                        message: 'Your order has been cancelled successfully.',
                        type: 'success'
                    });
                }

                // Redirect back to orders
                setTimeout(() => {
                    window.location.href = 'orders.php';
                }, 1500);

            } catch (error) {
                console.error('Error cancelling order:', error);
                button.disabled = false;
                button.textContent = 'Cancel Order';

                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Error',
                        message: error.message || 'Failed to cancel order.',
                        type: 'error'
                    });
                }
            }
        }
    </script>
</body>

</html>