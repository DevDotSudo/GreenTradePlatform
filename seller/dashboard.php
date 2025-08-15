<?php
session_start();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="text-success">Welcome, <span id="seller-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>!</h2>
                <p class="text-muted">This is your seller dashboard where you can manage your products and orders.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="card-title">Total Products</h3>
                        <h1 id="products-count">0</h1>
                        <a href="my_products.php" class="btn btn-success">Manage Products</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="card-title">Pending Orders</h3>
                        <h1 id="pending-orders-count">0</h1>
                        <a href="orders.php" class="btn btn-success">View Orders</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h3 class="card-title">Total Sales</h3>
                        <h1 id="total-sales">₱0.00</h1>
                    </div>
                </div>
            </div>
        </div>
        
        <h3 class="mb-3">Recent Orders</h3>
        <div class="card">
            <div class="card-body">
                <div id="recent-orders-container">
                    <!-- Recent orders will be loaded here -->
                    <div class="text-center py-5" id="loading">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading recent orders...</p>
                    </div>
                    
                    <div class="text-center py-5 d-none" id="no-orders">
                        <p>No orders yet</p>
                        <p class="text-muted">When customers place orders, they will appear here.</p>
                        <a href="add_product.php" class="btn btn-success">Add Your First Product</a>
                    </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            waitForFirebase(() => {
                loadDashboardData();
                loadRecentOrders();
            });
        });
        
        function loadDashboardData() {
            const sellerId = '<?php echo $_SESSION['user_id']; ?>';
            
            firebase.firestore().collection('products')
                .where('sellerId', '==', sellerId)
                .get()
                .then(snapshot => {
                    document.getElementById('products-count').textContent = snapshot.size;
                })
                .catch(error => {
                    console.error("Error getting product count: ", error);
                });
            
            firebase.firestore().collection('orders')
                .where('status', '==', 'Pending')
                .get()
                .then(snapshot => {
                    let pendingCount = 0;
                    snapshot.forEach(doc => {
                        const order = doc.data();
                        if (order.items.some(item => item.sellerId === sellerId)) {
                            pendingCount++;
                        }
                    });
                    document.getElementById('pending-orders-count').textContent = pendingCount;
                })
                .catch(error => {
                    console.error("Error getting pending orders count: ", error);
                });
            
            firebase.firestore().collection('orders')
                .where('status', '==', 'Delivered')
                .get()
                .then(snapshot => {
                    let totalSales = 0;
                    snapshot.forEach(doc => {
                        const order = doc.data();
                        order.items.forEach(item => {
                            if (item.sellerId === sellerId) {
                                totalSales += item.price * item.quantity;
                            }
                        });
                    });
                    document.getElementById('total-sales').textContent = `₱${totalSales.toFixed(2)}`;
                })
                .catch(error => {
                    console.error("Error getting total sales: ", error);
                });
        }
        
        function loadRecentOrders() {
            const sellerId = '<?php echo $_SESSION['user_id']; ?>';
            const recentOrdersContainer = document.getElementById('recent-orders-container');
            const loading = document.getElementById('loading');
            const noOrders = document.getElementById('no-orders');
            
            firebase.firestore().collection('orders')
                .orderBy('orderDate', 'desc')
                .limit(5)
                .get()
                .then(snapshot => {
                    loading.style.display = 'none';
                    if (snapshot.empty) {
                        noOrders.classList.remove('d-none');
                        return;
                    }
                    
                    const relevantOrders = [];
                    snapshot.forEach(doc => {
                        const order = { id: doc.id, ...doc.data() };
                        const sellerItems = order.items.filter(item => item.sellerId === sellerId);
                        if (sellerItems.length > 0) {
                            let sellerTotal = 0;
                            sellerItems.forEach(item => {
                                sellerTotal += item.price * item.quantity;
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
                                    <td>${new Date(order.orderDate.toDate()).toLocaleDateString()}</td>
                                    <td>${order.buyerName}</td>
                                    <td>${order.sellerItems.length}</td>
                                    <td>₱${order.sellerTotal.toFixed(2)}</td>
                                    <td><span class="badge ${getStatusBadgeClass(order.status)}">${order.status}</span></td>
                                    <td><a href="orders.php?id=${order.id}" class="btn btn-sm btn-outline-success">View</a></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    `;
                    recentOrdersContainer.appendChild(table);
                })
                .catch(error => {
                    loading.style.display = 'none';
                    console.error("Error getting recent orders: ", error);
                    recentOrdersContainer.innerHTML = `<div class="alert alert-danger">Error loading recent orders. Please try again later.</div>`;
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
    </script>
</body>
</html>
