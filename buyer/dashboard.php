<?php
session_start();
include '../includes/auth.php';
include '../includes/functions.php';

// Ensure user is logged in as a buyer
ensureUserLoggedIn('buyer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="text-success">Welcome back, <span id="buyer-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>!</h2>
                <p class="text-muted">Here's what's happening with your account today.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="card-title">My Cart</h3>
                        <div class="d-flex justify-content-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i data-feather="shopping-cart" class="text-primary"></i>
                            </div>
                        </div>
                        <h1 id="cart-count">0</h1>
                        <p class="text-muted">Items in cart</p>
                        <a href="cart.php" class="btn btn-outline-success">View my cart →</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="card-title">My Orders</h3>
                        <div class="d-flex justify-content-center mb-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i data-feather="package" class="text-warning"></i>
                            </div>
                        </div>
                        <h1 id="orders-count">0</h1>
                        <p class="text-muted">Active orders</p>
                        <a href="orders.php" class="btn btn-outline-success">View my orders →</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="card-title">Available Products</h3>
                        <div class="d-flex justify-content-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i data-feather="shopping-bag" class="text-success"></i>
                            </div>
                        </div>
                        <h1 id="products-count">0</h1>
                        <p class="text-muted">Products to browse</p>
                        <a href="products.php" class="btn btn-outline-success">Shop now →</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Recent Orders</h3>
                <a href="orders.php" class="text-success">View all orders →</a>
            </div>
            <div class="card-body">
                <div id="recent-orders-list">
                    <!-- Orders will be loaded here -->
                    <div class="text-center py-5" id="no-orders">
                        <img src="../assets/svg/no-orders.svg" alt="No orders" style="max-width: 120px;" class="mb-3">
                        <h4>No orders yet</h4>
                        <p class="text-muted">When you place orders, they will appear here.</p>
                        <a href="products.php" class="btn btn-success">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();
            
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
        });
        
        // Function to load recent orders
        function loadRecentOrders() {
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            
            // Query Firestore for recent orders
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
                        orderCard.className = 'card mb-3';
                        orderCard.innerHTML = `
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">Order #${doc.id.substring(0, 8)}</h5>
                                        <p class="text-muted mb-0">Placed on ${orderDate}</p>
                                    </div>
                                    <div>
                                        <span class="badge ${getStatusBadgeClass(order.status)}">${order.status}</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span>Total Amount: ₱${order.totalAmount.toFixed(2)}</span>
                                    <a href="orders.php?id=${doc.id}" class="btn btn-sm btn-outline-success">View Details</a>
                                </div>
                            </div>
                        `;
                        
                        ordersList.appendChild(orderCard);
                    });
                })
                .catch(error => {
                    console.error("Error getting recent orders: ", error);
                });
        }
        
        function getStatusBadgeClass(status) {
            switch(status) {
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
        
        // Function to get cart count
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
        
        // Function to get orders count
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
        
        // Function to get products count
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
