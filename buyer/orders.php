<?php
session_start();
include '../includes/auth.php';
include '../includes/functions.php';
ensureUserLoggedIn('buyer');
$orderId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <?php if ($orderId): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Order Details</h1>
                <a href="orders.php" class="btn btn-outline-success">
                    <i data-feather="arrow-left" class="me-1"></i> Back to Orders
                </a>
            </div>
            
            <div id="order-details-container">
                <!-- Order details will be loaded here -->
                <div class="text-center py-5" id="loading">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading order details...</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Orders List View -->
            <h1 class="mb-4">My Orders</h1>
            
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all">All Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pending-tab" data-bs-toggle="tab" href="#pending">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="processing-tab" data-bs-toggle="tab" href="#processing">Processing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="delivered-tab" data-bs-toggle="tab" href="#delivered">Delivered</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cancelled-tab" data-bs-toggle="tab" href="#cancelled">Cancelled</a>
                </li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="all">
                    <div id="all-orders-container">
                        <!-- All orders will be loaded here -->
                        <div class="text-center py-5" id="loading-all">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading orders...</p>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="pending">
                    <div id="pending-orders-container">
                        <!-- Pending orders will be loaded here -->
                        <div class="text-center py-5" id="loading-pending">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading pending orders...</p>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="processing">
                    <div id="processing-orders-container">
                        <!-- Processing orders will be loaded here -->
                        <div class="text-center py-5" id="loading-processing">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading processing orders...</p>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="delivered">
                    <div id="delivered-orders-container">
                        <!-- Delivered orders will be loaded here -->
                        <div class="text-center py-5" id="loading-delivered">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading delivered orders...</p>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="cancelled">
                    <div id="cancelled-orders-container">
                        <!-- Cancelled orders will be loaded here -->
                        <div class="text-center py-5" id="loading-cancelled">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading cancelled orders...</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();
            
            waitForFirebase(() => {
            <?php if ($orderId): ?>
                // Load single order details
                loadOrderDetails('<?php echo $orderId; ?>');
            <?php else: ?>
                // Load orders for each tab
                loadOrders('all');
                
                // Add tab change event listeners
                document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
                    tab.addEventListener('shown.bs.tab', function(e) {
                        const targetId = e.target.getAttribute('href').substring(1);
                        loadOrders(targetId);
                    });
                });
            <?php endif; ?>
            });
        });
        
        <?php if ($orderId): ?>
        // Function to load order details
        function loadOrderDetails(orderId) {
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            const orderDetailsContainer = document.getElementById('order-details-container');
            const loading = document.getElementById('loading');
            
            // Get order from Firestore
            firebase.firestore().collection('orders').doc(orderId).get()
                .then(doc => {
                    loading.style.display = 'none';
                    
                    if (!doc.exists) {
                        orderDetailsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                Order not found or you don't have permission to view it.
                            </div>
                        `;
                        return;
                    }
                    
                    const order = doc.data();
                    
                    // Verify this order belongs to the current user
                    if (order.buyerId !== userId) {
                        orderDetailsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                You don't have permission to view this order.
                            </div>
                        `;
                        return;
                    }
                    
                    // Format date
                    const orderDate = new Date(order.orderDate.toDate()).toLocaleString();
                    
                    // Create order details
                    orderDetailsContainer.innerHTML = `
                        <div class="card mb-4">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Order #${doc.id.substring(0, 8)}</h5>
                                    <p class="text-muted mb-0">Placed on ${orderDate}</p>
                                </div>
                                <span class="badge ${getStatusBadgeClass(order.status)}">${order.status}</span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6>Shipping Information</h6>
                                        <p class="mb-1"><strong>Name:</strong> ${order.shippingDetails.name}</p>
                                        <p class="mb-1"><strong>Address:</strong> ${order.shippingDetails.address}</p>
                                        <p class="mb-1"><strong>Phone:</strong> ${order.shippingDetails.phone}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Payment Information</h6>
                                        <p class="mb-1"><strong>Payment Method:</strong> Cash on Delivery</p>
                                        <p class="mb-1"><strong>Total Amount:</strong> ₱${order.totalAmount.toFixed(2)}</p>
                                    </div>
                                </div>
                                
                                <h6>Order Items</h6>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Seller</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="order-items-list">
                                            ${order.items.map(item => `
                                                <tr>
                                                    <td>${item.name}</td>
                                                    <td>${item.sellerName}</td>
                                                    <td>₱${item.price.toFixed(2)}</td>
                                                    <td>${item.quantity}</td>
                                                    <td class="text-end">₱${(item.price * item.quantity).toFixed(2)}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Subtotal</strong></td>
                                                <td class="text-end">₱${order.subtotal.toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Delivery Fee</strong></td>
                                                <td class="text-end">₱${order.deliveryFee.toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Total</strong></td>
                                                <td class="text-end"><strong>₱${order.totalAmount.toFixed(2)}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                ${order.status === 'Pending' ? `
                                    <div class="mt-3 text-end">
                                        <button id="cancel-order-btn" class="btn btn-danger">Cancel Order</button>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                    
                    // Add event listener for cancel button if order is pending
                    if (order.status === 'Pending') {
                        document.getElementById('cancel-order-btn').addEventListener('click', function() {
                            cancelOrder(orderId);
                        });
                    }
                })
                .catch(error => {
                    loading.style.display = 'none';
                    console.error("Error getting order details: ", error);
                    
                    orderDetailsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            Error loading order details. Please try again later.
                        </div>
                    `;
                });
        }
        
        // Function to cancel an order
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                firebase.firestore().collection('orders').doc(orderId).update({
                    status: 'Cancelled'
                })
                .then(() => {
                    alert('Order has been cancelled successfully.');
                    loadOrderDetails(orderId);
                })
                .catch(error => {
                    console.error("Error cancelling order: ", error);
                    alert('Error cancelling order. Please try again later.');
                });
            }
        }
        <?php else: ?>
        // Function to load orders
        function loadOrders(tabId) {
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            const container = document.getElementById(`${tabId}-orders-container`);
            const loading = document.getElementById(`loading-${tabId}`);
            
            // Don't reload if already loaded
            if (!loading || loading.style.display === 'none') {
                return;
            }
            
            // Create a query to Firestore
            let query = firebase.firestore().collection('orders')
                .where('buyerId', '==', userId)
                .orderBy('orderDate', 'desc');
            
            // Add status filter for specific tabs
            if (tabId === 'pending') {
                query = query.where('status', '==', 'Pending');
            } else if (tabId === 'processing') {
                query = query.where('status', 'in', ['Processing', 'Out for Delivery']);
            } else if (tabId === 'delivered') {
                query = query.where('status', '==', 'Delivered');
            } else if (tabId === 'cancelled') {
                query = query.where('status', '==', 'Cancelled');
            }
            
            // Execute the query
            query.get()
                .then(snapshot => {
                    loading.style.display = 'none';
                    
                    if (snapshot.empty) {
                        container.innerHTML = `
                            <div class="text-center py-5">
                                <i data-feather="package" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                <h4>No orders found</h4>
                                <p class="text-muted">You don't have any ${tabId !== 'all' ? tabId + ' ' : ''}orders yet.</p>
                                <a href="products.php" class="btn btn-success mt-2">Browse Products</a>
                            </div>
                        `;
                        feather.replace();
                        return;
                    }
                    
                    // Create and append order elements
                    container.innerHTML = '';
                    
                    snapshot.forEach(doc => {
                        const order = doc.data();
                        const orderDate = new Date(order.orderDate.toDate()).toLocaleDateString();
                        
                        const orderCard = document.createElement('div');
                        orderCard.className = 'card mb-3';
                        orderCard.innerHTML = `
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-1">Order #${doc.id.substring(0, 8)}</h5>
                                        <p class="text-muted mb-3">Placed on ${orderDate}</p>
                                        
                                        <p class="mb-1"><strong>Items:</strong> ${order.items.length}</p>
                                        <p class="mb-3"><strong>Total:</strong> ₱${order.totalAmount.toFixed(2)}</p>
                                        
                                        <a href="orders.php?id=${doc.id}" class="btn btn-outline-success">View Details</a>
                                        
                                        ${order.status === 'Pending' ? `
                                            <button class="btn btn-outline-danger ms-2 cancel-order" data-id="${doc.id}">Cancel Order</button>
                                        ` : ''}
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <span class="badge ${getStatusBadgeClass(order.status)} mb-2">${order.status}</span>
                                        <p class="text-muted mb-0">
                                            <i data-feather="truck" class="feather-sm me-1"></i> 
                                            ${getStatusMessage(order.status)}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        container.appendChild(orderCard);
                    });
                    
                    // Initialize Feather icons for dynamically added content
                    feather.replace();
                    
                    // Add event listeners for cancel buttons
                    document.querySelectorAll('.cancel-order').forEach(button => {
                        button.addEventListener('click', function() {
                            const orderId = this.getAttribute('data-id');
                            cancelOrder(orderId);
                        });
                    });
                })
                .catch(error => {
                    loading.style.display = 'none';
                    console.error("Error getting orders: ", error);
                    
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            Error loading orders. Please try again later.
                        </div>
                    `;
                });
        }
        
        // Function to cancel an order from the list view
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                firebase.firestore().collection('orders').doc(orderId).update({
                    status: 'Cancelled'
                })
                .then(() => {
                    alert('Order has been cancelled successfully.');
                    
                    // Reload orders for all tabs
                    document.getElementById('loading-all').style.display = 'block';
                    document.getElementById('all-orders-container').innerHTML = '';
                    
                    document.getElementById('loading-pending').style.display = 'block';
                    document.getElementById('pending-orders-container').innerHTML = '';
                    
                    document.getElementById('loading-cancelled').style.display = 'block';
                    document.getElementById('cancelled-orders-container').innerHTML = '';
                    
                    // Reload the current tab
                    const activeTab = document.querySelector('.nav-link.active').getAttribute('href').substring(1);
                    loadOrders(activeTab);
                })
                .catch(error => {
                    console.error("Error cancelling order: ", error);
                    alert('Error cancelling order. Please try again later.');
                });
            }
        }
        <?php endif; ?>
        
        // Helper function to get status badge class
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
        
        // Helper function to get status message
        function getStatusMessage(status) {
            switch(status) {
                case 'Pending':
                    return 'Awaiting confirmation';
                case 'Processing':
                    return 'Order is being prepared';
                case 'Out for Delivery':
                    return 'On the way to you';
                case 'Delivered':
                    return 'Successfully delivered';
                case 'Cancelled':
                    return 'Order was cancelled';
                default:
                    return '';
            }
        }
    </script>
</body>
</html>
