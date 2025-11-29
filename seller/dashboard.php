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
    <title>Seller Dashboard - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid px-3 px-md-4 mt-4">
        <div class="welcome-section mb-5">
            <div class="welcome-card">
                <div class="welcome-content">
                    <h1 class="welcome-title">
                        Welcome back, <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>! 👋
                    </h1>
                    <p class="welcome-subtitle">Manage your products and track your sales performance</p>
                </div>
            </div>
        </div>

        <div class="stats-grid mb-5">
            <div class="stat-card">
                <div class="stat-icon products-icon">📦</div>
                <div class="stat-content">
                    <h3 class="stat-title">Total Products</h3>
                    <div class="stat-number" id="products-count">0</div>
                    <p class="stat-description">Active listings</p>
                    <a href="my_products.php" class="btn btn-outline-success">Manage Products →</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orders-icon">🛒</div>
                <div class="stat-content">
                    <h3 class="stat-title">Pending Orders</h3>
                    <div class="stat-number" id="pending-orders-count">0</div>
                    <p class="stat-description">Awaiting fulfillment</p>
                    <a href="orders.php" class="btn btn-outline-success">View Orders →</a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon sales-icon">💰</div>
                <div class="stat-content">
                    <h3 class="stat-title">Total Sales</h3>
                    <div class="stat-number" id="total-sales">₱0.00</div>
                    <p class="stat-description">Revenue from completed orders</p>
                    <a href="orders.php" class="btn btn-outline-success">View Details →</a>
                </div>
            </div>
        </div>

        <div class="recent-orders-section">
            <div class="section-header">
                <h2 class="section-title">Recent Orders</h2>
                <a href="orders.php" class="section-link">View all orders →</a>
            </div>

            <div class="orders-container">
                <div id="recent-orders-container">
                    <!-- Orders will be loaded here -->
                </div>

                <div class="empty-state d-none" id="no-orders">
                    <div class="empty-icon">📦</div>
                    <h3 class="empty-title">No orders yet</h3>
                    <p class="empty-description">When customers place orders for your products, they will appear here.</p>
                    <a href="add_product.php" class="btn btn-success">Add Your First Product</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script src="../assets/js/services/ordersService.js"></script>
    <script src="../assets/js/services/orderService.js"></script>
    <script src="../assets/js/services/cartService.js"></script>
    <script src="../assets/js/orderManager.js"></script>
    
    <script>
        // Load viewed orders from localStorage and create Set
        let viewedOrders = new Set(JSON.parse(localStorage.getItem('viewedOrders') || '[]'));
        
        // Save viewed orders to localStorage whenever it changes
        const saveViewedOrders = () => {
            localStorage.setItem('viewedOrders', JSON.stringify([...viewedOrders]));
        };

        document.addEventListener('DOMContentLoaded', function() {
            waitForFirebase(() => {
                // Initialize services after Firebase is ready
                if (!window.ordersService) {
                    window.ordersService = new OrdersService();
                }
                if (!window.orderService) {
                    window.orderService = new OrderService();
                }
                if (!window.cartService) {
                    window.cartService = new CartService();
                }
                if (!window.orderManager) {
                    window.orderManager = new OrderManager();
                }
            });
        });
    </script>

    <style>
        .welcome-section {
            margin-bottom: var(--space-8);
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            border-radius: var(--radius-xl);
            padding: var(--space-8);
            color: white;
            box-shadow: var(--shadow-xl);
        }

        .welcome-content {
            text-align: center;
            margin-bottom: var(--space-6);
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: var(--space-3);
        }

        .user-name {
            color: #e8f5e8;
        }

        .welcome-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            margin: 0;
        }

        .welcome-actions {
            display: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--space-4);
            margin-bottom: var(--space-8);
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-6);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all var(--transition-normal);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: var(--space-3);
            display: block;
            opacity: 0.8;
        }

        .products-icon {
            color: var(--success-500);
        }

        .orders-icon {
            color: var(--warning-500);
        }

        .sales-icon {
            color: var(--primary-500);
        }

        .stat-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-2);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-600);
            margin-bottom: var(--space-2);
            line-height: 1;
        }

        .stat-description {
            color: var(--gray-600);
            margin-bottom: var(--space-4);
        }

        .recent-orders-section {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-6);
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }

        .section-link {
            color: var(--primary-600);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition-fast);
        }

        .section-link:hover {
            color: var(--primary-700);
        }

        .orders-container {
            padding: var(--space-6);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: var(--space-3);
                padding-right: var(--space-3);
            }

            .welcome-title {
                font-size: 2rem;
            }

            .welcome-actions {
                display: none;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: var(--space-4);
                margin-bottom: var(--space-6);
            }

            .stat-card {
                padding: var(--space-5);
            }

            .stat-number {
                font-size: 2.25rem;
            }

            .section-header {
                flex-direction: column;
                gap: var(--space-3);
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .welcome-card {
                padding: var(--space-6);
            }

            .welcome-title {
                font-size: 1.75rem;
            }

            .stat-card {
                padding: var(--space-4);
            }

            .stat-number {
                font-size: 2rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            waitForFirebase(() => {
                loadDashboardData();
                loadRecentOrders();
            });
        });

        async function loadDashboardData() {
            const sellerId = '<?php echo $_SESSION['user_id']; ?>';

            if (window.loadingOverlay) {
                window.loadingOverlay.show('Loading dashboard...');
            }

            try {
                // Initialize order manager for stats
                if (window.orderManager && typeof window.orderManager.init === 'function') {
                    await window.orderManager.init();
                }

                // Get order stats using the order manager
                const stats = await window.orderManager.getOrderStats(sellerId);

                // Get products count separately
                const productsSnapshot = await firebase.firestore().collection('products').where('sellerId', '==', sellerId).get();
                document.getElementById('products-count').textContent = productsSnapshot.size;

                document.getElementById('pending-orders-count').textContent = stats.pending || 0;
                document.getElementById('total-sales').textContent = `₱${(stats.totalSpent || 0).toFixed(2)}`;

                if (window.loadingOverlay) window.loadingOverlay.hide();
            } catch (error) {
                if (window.loadingOverlay) window.loadingOverlay.hide();
                console.error("Error loading dashboard data: ", error);

                // Fallback to old method if order manager fails
                try {
                    const [productsSnapshot, pendingOrdersSnapshot, deliveredOrdersSnapshot] = await Promise.all([
                        firebase.firestore().collection('products').where('sellerId', '==', sellerId).get(),
                        firebase.firestore().collection('orders').where('status', '==', 'Pending').get(),
                        firebase.firestore().collection('orders').where('status', '==', 'Delivered').get()
                    ]);

                    document.getElementById('products-count').textContent = productsSnapshot.size;

                    let pendingCount = 0;
                    pendingOrdersSnapshot.forEach(doc => {
                        const order = doc.data();
                        if (order.items.some(item => item.sellerId === sellerId)) {
                            pendingCount++;
                        }
                    });
                    document.getElementById('pending-orders-count').textContent = pendingCount;

                    let totalSales = 0;
                    deliveredOrdersSnapshot.forEach(doc => {
                        const order = doc.data();
                        order.items.forEach(item => {
                            if (item.sellerId === sellerId) {
                                totalSales += (Number(item.price) || 0) * (Number(item.quantity) || 0);
                            }
                        });
                    });
                    document.getElementById('total-sales').textContent = `₱${totalSales.toFixed(2)}`;
                } catch (fallbackError) {
                    console.error("Fallback dashboard loading also failed:", fallbackError);
                }
            }
        }

        async function loadRecentOrders() {
            const sellerId = '<?php echo $_SESSION['user_id']; ?>';
            const recentOrdersContainer = document.getElementById('recent-orders-container');
            const noOrders = document.getElementById('no-orders');

            try {
                // Use order manager to get recent orders
                const orders = await window.orderManager.getRecentOrders(sellerId, 5);

                if (!orders || orders.length === 0) {
                    noOrders.classList.remove('d-none');
                    return;
                }

                recentOrdersContainer.innerHTML = '';
                const table = document.createElement('table');
                table.className = 'table table-hover';
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${orders.map(order => {
                            const statusInfo = window.orderManager.getStatusDisplayInfo(order.status);
                            const orderDate = window.orderManager.formatDateTime(order.createdAt);

                            // Calculate seller-specific totals
                            let sellerTotal = 0;
                            let sellerItemsCount = 0;
                            order.items.forEach(item => {
                                if (item.sellerId === sellerId) {
                                    sellerTotal += (Number(item.price) || 0) * (Number(item.quantity) || 0);
                                    sellerItemsCount += Number(item.quantity) || 0;
                                }
                            });

                            return `
                                <tr>
                                    <td>#${order.id.substring(0, 8)}</td>
                                    <td>${orderDate}</td>
                                    <td>${order.shippingInfo?.name || 'N/A'}</td>
                                    <td>${sellerItemsCount}</td>
                                    <td>₱${sellerTotal.toFixed(2)}</td>
                                    <td><span class="badge ${statusInfo.class}">${statusInfo.label}</span></td>
                                    <td><a href="order_details.php?id=${order.id}" class="btn btn-sm btn-outline-success view-details-btn" data-order-id="${order.id}">View</a></td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                `;
                recentOrdersContainer.appendChild(table);
            } catch (error) {
                console.error("Error getting recent orders: ", error);

                // Fallback to old method
                try {
                    const snapshot = await firebase.firestore().collection('orders')
                        .orderBy('createdAt', 'desc')
                        .limit(5)
                        .get();

                    if (snapshot.empty) {
                        noOrders.classList.remove('d-none');
                        return;
                    }

                    const relevantOrders = [];
                    snapshot.forEach(doc => {
                        const order = {
                            id: doc.id,
                            ...doc.data()
                        };
                        const sellerItems = order.items.filter(item => item.sellerId === sellerId);
                        if (sellerItems.length > 0) {
                            let sellerTotal = 0;
                            sellerItems.forEach(item => {
                                sellerTotal += (Number(item.price) || 0) * (Number(item.quantity) || 0);
                            });
                            order.sellerItems = sellerItems;
                            order.sellerTotal = sellerTotal;
                            relevantOrders.push(order);
                        }
                    });

                    if (relevantOrders.length === 0) {
                        noOrders.classList.remove('d-none');
                        return;
                    }

                    recentOrdersContainer.innerHTML = '';
                    const table = document.createElement('table');
                    table.className = 'table table-hover';
                    table.innerHTML = `
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${relevantOrders.map(order => `
                                <tr>
                                    <td>#${order.id.substring(0, 8)}</td>
                                    <td>${window.orderManager.formatDateTime(order.createdAt)}</td>
                                    <td>${order.shippingInfo?.name || 'N/A'}</td>
                                    <td>${order.sellerItems.length}</td>
                                    <td>₱${(order.sellerTotal || 0).toFixed(2)}</td>
                                    <td><span class="badge ${getStatusBadgeClass(order.status)}">${order.status}</span></td>
                                    <td><a href="order_details.php?id=${order.id}" class="btn btn-sm btn-outline-success view-details-btn" data-order-id="${order.id}">View</a></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    `;
                    recentOrdersContainer.appendChild(table);
                } catch (fallbackError) {
                    console.error("Fallback recent orders loading also failed:", fallbackError);
                    recentOrdersContainer.innerHTML = `<div class="alert alert-danger">Error loading recent orders. Please try again later.</div>`;
                }
            }
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'Pending':
                    return 'bg-warning';
                case 'Processing':
                    return 'bg-info';
                case 'Out for Delivery':
                    return 'bg-primary';
                case 'Delivered':
                    return 'bg-success';
                case 'Cancelled':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        }

        // Event delegation for view details buttons
        document.addEventListener('click', async function(e) {
            if (e.target.classList.contains('view-details-btn') && !viewedOrders.has(e.target.dataset.orderId)) {
                // Mark order as viewed when clicked
                const orderId = e.target.dataset.orderId;
                viewedOrders.add(orderId);
                saveViewedOrders(); // Save to localStorage
                
                // Update button appearance immediately
                const hasBeenViewed = viewedOrders.has(orderId);
                const viewButtonText = hasBeenViewed ? 'Already Checked' : 'View';
                const viewButtonClass = hasBeenViewed ? 'btn btn-sm btn-secondary' : 'btn btn-sm btn-outline-success';
                
                e.target.textContent = viewButtonText;
                e.target.className = viewButtonClass;
                e.target.setAttribute('onclick', 'return false;');
            }
        });
    </script>
</body>

</html>