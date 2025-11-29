<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';
include '../includes/functions.php';

ensureUserLoggedIn('seller');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Green Trade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">My Orders</h1>
                <p class="page-subtitle">Manage orders for your products</p>
            </div>

            <!-- Orders Filters -->
            <div class="orders-filters">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-status="all">All Orders</button>
                    <button class="filter-tab" data-status="Pending">Pending</button>
                    <button class="filter-tab" data-status="Processing">Processing</button>
                    <button class="filter-tab" data-status="Out for Delivery">Out for Delivery</button>
                    <button class="filter-tab" data-status="Cancelled">Cancelled</button>
                </div>
            </div>

            <!-- Orders Container -->
            <div class="orders-container">
                <div id="orders-list">
                    <!-- Orders will be loaded here -->
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <div class="loading-text">Loading orders...</div>
                    </div>
                </div>

                <!-- Empty State -->
                <div class="empty-state d-none" id="no-orders">
                    <div class="empty-icon">📦</div>
                    <h3 class="empty-title">No orders yet</h3>
                    <p class="empty-description">When customers place orders for your products, they will appear here.</p>
                    <a href="add_product.php" class="btn btn-primary">Add Your First Product</a>
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
    <script src="../assets/js/services/ordersService.js"></script>
    <script src="../assets/js/orderManager.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            waitForFirebase(() => {
                // Initialize services after Firebase is ready
                if (!window.orderService) {
                    window.orderService = new OrderService();
                }
                if (!window.ordersService) {
                    window.ordersService = new OrdersService();
                }
                if (!window.orderManager) {
                    window.orderManager = new OrderManager();
                }
            });
        });
    </script>

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

        .orders-filters {
            margin-bottom: var(--space-6);
        }

        .filter-tabs {
            display: flex;
            gap: var(--space-2);
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-tab {
            padding: var(--space-3) var(--space-6);
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            color: var(--gray-700);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .filter-tab:hover {
            background: var(--gray-50);
            border-color: var(--primary-300);
        }

        .filter-tab.active {
            background: var(--primary-500);
            border-color: var(--primary-500);
            color: white;
        }

        .orders-container {
            position: relative;
        }

        .order-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            margin-bottom: var(--space-6);
            overflow: hidden;
            transition: all var(--transition-normal);
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
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
            font-size: 1.25rem;
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

        .order-items {
            margin-bottom: var(--space-6);
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
            width: 60px;
            height: 60px;
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
        }

        .order-actions {
            display: flex;
            gap: var(--space-3);
            flex-wrap: wrap;
        }

        .status-select {
            padding: var(--space-2) var(--space-3);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            background: white;
            font-size: 0.875rem;
        }

        .empty-state {
            text-align: center;
            padding: var(--space-12) var(--space-6);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: var(--space-4);
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-2);
        }

        .empty-description {
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

            .filter-tabs {
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: var(--space-2);
            }

            .filter-tab {
                white-space: nowrap;
                flex-shrink: 0;
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
                width: 50px;
                height: 50px;
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
        let currentFilter = 'all';
        let allOrders = [];
        
        // Load viewed orders from localStorage and create Set
        let viewedOrders = new Set(JSON.parse(localStorage.getItem('viewedOrders') || '[]'));
        
        // Save viewed orders to localStorage whenever it changes
        const saveViewedOrders = () => {
            localStorage.setItem('viewedOrders', JSON.stringify([...viewedOrders]));
        };

        document.addEventListener('DOMContentLoaded', function() {
            waitForFirebase(() => {
                initializeOrders();
            });
        });

        async function initializeOrders() {
            try {
                // Wait for services to be initialized by waitForFirebase
                await window.orderService.init();
                await loadOrders();
                setupFilters();
            } catch (error) {
                console.error('Error initializing orders:', error);
                showError('Failed to load orders. Please refresh the page.');
            }
        }

        function setupFilters() {
            const filterTabs = document.querySelectorAll('.filter-tab');

            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Update active tab
                    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Update filter and reload orders
                    currentFilter = this.dataset.status;
                    filterOrders();
                });
            });
        }

        async function loadOrders() {
            const ordersList = document.getElementById('orders-list');
            const noOrders = document.getElementById('no-orders');

            try {
                // Show loading
                ordersList.innerHTML = `
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <div class="loading-text">Loading orders...</div>
                    </div>
                `;
                noOrders.classList.add('d-none');

                // Load orders for seller using ordersService
                const sellerId = '<?php echo $_SESSION['user_id']; ?>';
                const snapshot = await firebase.firestore().collection('orders').orderBy('createdAt', 'desc').get();
                
                const orders = [];
                snapshot.forEach(doc => {
                    const order = doc.data();
                    // Check if order contains items from this seller
                    if (order.items && order.items.some(item => item.sellerId === sellerId)) {
                        // Add seller-specific data
                        order.sellerItems = order.items.filter(item => item.sellerId === sellerId);
                        order.sellerSubtotal = order.sellerItems.reduce((sum, item) =>
                            sum + ((Number(item.price) || 0) * (Number(item.quantity) || 0)), 0);
                        
                        orders.push({
                            id: doc.id,
                            ...order
                        });
                    }
                });
                
                allOrders = orders;

                // Hide loading
                if (orders.length === 0) {
                    noOrders.classList.remove('d-none');
                    return;
                }

                // Display orders
                displayOrders(orders);

            } catch (error) {
                console.error('Error loading orders:', error);
                showError('Failed to load orders. Please try again.');
            }
        }

        function filterOrders() {
            let filteredOrders = allOrders;

            if (currentFilter !== 'all') {
                filteredOrders = allOrders.filter(order => order.status === currentFilter);
            }

            displayOrders(filteredOrders);

            // Show empty state if no orders match filter
            const noOrders = document.getElementById('no-orders');
            if (filteredOrders.length === 0) {
                noOrders.classList.remove('d-none');
            } else {
                noOrders.classList.add('d-none');
            }
        }

        function displayOrders(orders) {
            const ordersList = document.getElementById('orders-list');

            ordersList.innerHTML = '';

            orders.forEach(order => {
                const orderCard = createOrderCard(order);
                ordersList.appendChild(orderCard);
            });
        }

        function createOrderCard(order) {
            const card = document.createElement('div');
            card.className = 'order-card';

            const statusInfo = window.orderService.getStatusDisplayInfo(order.status);
            const orderDate = window.orderManager.formatDateTime(order.createdAt);

            // Display seller-specific items
            let itemsHtml = '';
            if (order.sellerItems && order.sellerItems.length > 0) {
                order.sellerItems.forEach(item => {
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
                                <div class="item-meta">₱${Number(item.price).toFixed(2)} × ${item.quantity} | Buyer: ${escapeHtml(order.shippingInfo?.name || order.buyerName || 'Unknown')}</div>
                            </div>
                            <div class="item-price">₱${itemTotal}</div>
                        </div>`;
                });
            }

            card.innerHTML = `
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
                    <div class="order-items">
                        ${itemsHtml}
                    </div>

                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Your Items Total</span>
                            <span>₱${(order.sellerSubtotal || 0).toFixed(2)}</span>
                        </div>
                    </div>

                    <div class="order-actions">
                        ${getOrderActions(order)}
                    </div>
                </div>
            `;

            return card;
        }

        function getOrderActions(order) {
            let actions = '';

            // Add status update options for non-delivered orders
            if (order.status !== 'Delivered' && order.status !== 'Cancelled') {
                const validStatuses = ['Pending', 'Processing', 'Out for Delivery', 'Cancelled']
                    .filter(status => status !== order.status);

                if (validStatuses.length > 0) {
                    actions += `<select class="status-select me-2" data-order-id="${order.id}">
                        <option value="">Update Status</option>
                        ${validStatuses.map(status => `<option value="${status}">${status}</option>`).join('')}
                    </select>`;
                    actions += `<button class="btn btn-primary btn-sm update-status" data-order-id="${order.id}">
                        Update
                    </button>`;
                }
            }

            const hasBeenViewed = viewedOrders.has(order.id);
            const viewButtonText = hasBeenViewed ? 'Already Checked' : 'View Details';
            const viewButtonClass = hasBeenViewed ? 'btn btn-secondary btn-sm' : 'btn btn-outline-primary btn-sm';
            
            actions += `<a href="order_details.php?id=${order.id}" class="${viewButtonClass} view-details-btn" data-order-id="${order.id}" ${hasBeenViewed ? 'onclick="return false;"' : ''}>
                ${viewButtonText}
            </a>`;

            return actions;
        }

        function showError(message) {
            const ordersList = document.getElementById('orders-list');
            ordersList.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">⚠️</div>
                    <h3 class="empty-title">Error</h3>
                    <p class="empty-description">${escapeHtml(message)}</p>
                    <button class="btn btn-primary" onclick="loadOrders()">Try Again</button>
                </div>
            `;
            document.getElementById('no-orders').classList.add('d-none');
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
            if (e.target.classList.contains('update-status')) {
                const orderId = e.target.dataset.orderId;
                const select = e.target.previousElementSibling;
                const newStatus = select.value;
                if (newStatus) {
                    await handleUpdateStatus(orderId, newStatus, e.target);
                }
            }
            
            if (e.target.classList.contains('view-details-btn') && !viewedOrders.has(e.target.dataset.orderId)) {
                // Mark order as viewed when clicked
                const orderId = e.target.dataset.orderId;
                viewedOrders.add(orderId);
                saveViewedOrders(); // Save to localStorage
                
                // Update button appearance immediately
                const hasBeenViewed = viewedOrders.has(orderId);
                const viewButtonText = hasBeenViewed ? 'Already Checked' : 'View Details';
                const viewButtonClass = hasBeenViewed ? 'btn btn-secondary btn-sm' : 'btn btn-outline-primary btn-sm';
                
                e.target.textContent = viewButtonText;
                e.target.className = viewButtonClass;
                e.target.setAttribute('onclick', 'return false;');
            }
        });

        async function handleUpdateStatus(orderId, newStatus, button) {
            try {
                button.disabled = true;
                button.textContent = 'Updating...';

                await window.orderService.updateOrderStatus(orderId, newStatus);

                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Status Updated',
                        message: `Order status updated to ${newStatus}.`,
                        type: 'success'
                    });
                }

                // Reload orders
                await loadOrders();

            } catch (error) {
                console.error('Error updating status:', error);
                button.disabled = false;
                button.textContent = 'Update';

                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Error',
                        message: error.message || 'Failed to update order status.',
                        type: 'error'
                    });
                }
            }
        }
    </script>
</body>

</html>