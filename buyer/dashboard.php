<?php
session_start();
include '../includes/auth.php';
include '../includes/functions.php';

ensureUserLoggedIn('buyer');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - Green Trade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="welcome-content">
                    <h1 class="welcome-title">
                        Welcome back, <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>! 👋
                    </h1>
                    <p class="welcome-subtitle">Here's what's happening with your account today.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon cart-icon">🛒</div>
                    <div class="stat-content">
                        <h3 class="stat-title">My Cart</h3>
                        <div class="stat-number" id="cart-count">0</div>
                        <p class="stat-description">Items in cart</p>
                        <a href="cart.php" class="btn btn-outline-primary">View my cart →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orders-icon">📦</div>
                    <div class="stat-content">
                        <h3 class="stat-title">My Orders</h3>
                        <div class="stat-number" id="orders-count">0</div>
                        <p class="stat-description">Active orders</p>
                        <a href="orders.php" class="btn btn-outline-primary">View my orders →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon products-icon">🛍️</div>
                    <div class="stat-content">
                        <h3 class="stat-title">Available Products</h3>
                        <div class="stat-number" id="products-count">0</div>
                        <p class="stat-description">Products to browse</p>
                        <a href="products.php" class="btn btn-outline-primary">Shop now →</a>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="recent-orders-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Orders</h2>
                    <a href="orders.php" class="section-link">View all orders →</a>
                </div>

                <div class="orders-container">
                    <div id="recent-orders-list">
                        <!-- Orders will be loaded here -->
                        <div class="empty-state" id="no-orders">
                            <div class="empty-icon">📦</div>
                            <h3 class="empty-title">No orders yet</h3>
                            <p class="empty-description">When you place orders, they will appear here.</p>
                            <a href="products.php" class="btn btn-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dialogs.js"></script>

    <style>
        .main-content {
            padding: var(--space-8) 0;
            min-height: calc(100vh - 200px);
        }

        .welcome-section {
            margin-bottom: var(--space-8);
        }

        .welcome-content {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: var(--space-3);
        }

        .user-name {
            color: var(--primary-600);
        }

        .welcome-subtitle {
            font-size: 1.125rem;
            color: var(--gray-600);
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--space-6);
            margin-bottom: var(--space-12);
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: var(--space-8);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            transition: all var(--transition-normal);
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: var(--space-4);
            display: block;
        }

        .cart-icon {
            color: var(--primary-500);
        }

        .orders-icon {
            color: var(--warning-500);
        }

        .products-icon {
            color: var(--success-500);
        }

        .stat-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-2);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-600);
            margin-bottom: var(--space-2);
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

        .order-card {
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            padding: var(--space-6);
            margin-bottom: var(--space-4);
            border: 1px solid var(--gray-200);
            transition: all var(--transition-fast);
        }

        .order-card:hover {
            background: white;
            box-shadow: var(--shadow-md);
        }

        .order-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-4);
        }

        .order-info h4 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-1);
        }

        .order-date {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .order-status {
            padding: var(--space-1) var(--space-3);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .order-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: var(--space-4);
            border-top: 1px solid var(--gray-200);
        }

        .order-total {
            font-weight: 600;
            color: var(--gray-900);
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
            .main-content {
                padding: var(--space-6) 0;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: var(--space-4);
            }

            .stat-card {
                padding: var(--space-6);
            }

            .stat-number {
                font-size: 2.5rem;
            }

            .section-header {
                flex-direction: column;
                gap: var(--space-3);
                text-align: center;
            }

            .order-header {
                flex-direction: column;
                gap: var(--space-3);
                text-align: center;
            }

            .order-footer {
                flex-direction: column;
                gap: var(--space-3);
                text-align: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            waitForFirebase(() => {
                loadDashboardData();
            });
        });

        function loadDashboardData() {
            // Load cart count
            getCartCount().then(count => {
                document.getElementById('cart-count').textContent = count;
            });

            // Load orders count
            getOrdersCount().then(count => {
                document.getElementById('orders-count').textContent = count;
            });

            // Load products count
            getProductsCount().then(count => {
                document.getElementById('products-count').textContent = count;
            });

            // Load recent orders
            loadRecentOrders();
        }

        function loadRecentOrders() {
            const userId = '<?php echo $_SESSION['user_id']; ?>';

            firebase.firestore().collection('orders')
                .where('buyerId', '==', userId)
                .orderBy('orderDate', 'desc')
                .limit(3)
                .get()
                .then(snapshot => {
                    if (snapshot.empty) {
                        document.getElementById('no-orders').style.display = 'block';
                        return;
                    }

                    document.getElementById('no-orders').style.display = 'none';
                    const ordersList = document.getElementById('recent-orders-list');

                    snapshot.forEach(doc => {
                        const order = doc.data();
                        const orderDate = new Date(order.orderDate.toDate()).toLocaleDateString();

                        const orderCard = document.createElement('div');
                        orderCard.className = 'order-card';
                        orderCard.innerHTML = `
                            <div class="order-header">
                                <div class="order-info">
                                    <h4>Order #${doc.id.substring(0, 8)}</h4>
                                    <p class="order-date">Placed on ${orderDate}</p>
                                </div>
                                <span class="order-status ${getStatusBadgeClass(order.status)}">${order.status}</span>
                            </div>
                            <div class="order-footer">
                                <span class="order-total">Total Amount: ₱${order.totalAmount.toFixed(2)}</span>
                                <a href="orders.php?id=${doc.id}" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        `;

                        ordersList.appendChild(orderCard);
                    });
                })
                .catch(error => {
                    console.error("Error getting recent orders: ", error);
                    showToast({
                        title: 'Error',
                        message: 'Failed to load recent orders. Please try again.',
                        type: 'error'
                    });
                });
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'Pending':
                    return 'badge-warning';
                case 'Processing':
                    return 'badge-info';
                case 'Out for Delivery':
                    return 'badge-primary';
                case 'Delivered':
                    return 'badge-success';
                case 'Cancelled':
                    return 'badge-error';
                default:
                    return 'badge-secondary';
            }
        }

        function getCartCount() {
            const userId = '<?php echo $_SESSION['user_id']; ?>';

            return firebase.firestore().collection('carts')
                .where('userId', '==', userId)
                .get()
                .then(snapshot => {
                    if (snapshot.empty) {
                        return 0;
                    }

                    let totalItems = 0;
                    snapshot.forEach(doc => {
                        const cart = doc.data();
                        if (cart.items) {
                            totalItems += cart.items.length;
                        }
                    });

                    return totalItems;
                })
                .catch(error => {
                    console.error("Error getting cart count: ", error);
                    return 0;
                });
        }

        function getOrdersCount() {
            const userId = '<?php echo $_SESSION['user_id']; ?>';

            return firebase.firestore().collection('orders')
                .where('buyerId', '==', userId)
                .where('status', 'in', ['Pending', 'Processing', 'Out for Delivery'])
                .get()
                .then(snapshot => {
                    return snapshot.size;
                })
                .catch(error => {
                    console.error("Error getting orders count: ", error);
                    return 0;
                });
        }

        function getProductsCount() {
            return firebase.firestore().collection('products')
                .get()
                .then(snapshot => {
                    return snapshot.size;
                })
                .catch(error => {
                    console.error("Error getting products count: ", error);
                    return 0;
                });
        }
    </script>
</body>

</html>