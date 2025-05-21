<?php
session_start();
include '../includes/auth.php';
include '../includes/functions.php';

// Ensure user is logged in as a seller
ensureUserLoggedIn('seller');

// Check if viewing a specific order
$orderId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <?php if ($orderId): ?>
            <!-- Single Order View -->
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
            <h1 class="mb-4">Manage Orders</h1>
            
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
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="update-status-form">
                        <div class="mb-3">
                            <label for="order-status" class="form-label">Select new status</label>
                            <select class="form-select" id="order-status" required>
                                <option value="Pending">Pending</option>
                                <option value="Processing">Processing</option>
                                <option value="Out for Delivery">Out for Delivery</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirm-update-status">Update Status</button>
                </div>
            </div>
        </div>
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
        
        let currentOrderId = null;
        
        <?php if ($orderId): ?>
        // Function to load order details
        function loadOrderDetails(orderId) {
            const sellerId = '<?php echo $_SESSION['user_id']; ?>';
            const orderDetailsContainer = document.getElementById('order-details-container');
            const loading = document.getElementById('loading');
            
            // Get order from Firestore
            firebase.firestore().collection('orders').doc(orderId).get()
                .then(doc => {
                    loading.style.display = 'none';
                    
                    if (!doc.exists) {
                        orderDetailsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                Order not found.
                            </div>
                        `;
                        return;
                    }
                    
                    const order = doc.data();
                    
                    // Check if any items in this order belong to the current seller
                    const sellerItems = order.items.filter(item => item.sellerId === sellerId);
                    
                    if (sellerItems.length === 0) {
                        orderDetailsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                This order does not contain any items from your inventory.
                            </div>
                        `;
                        return;
                    }
                    
                    // Calculate total for this seller's items only
                    let sellerTotal = 0;
                    sellerItems.forEach(item => {
                        sellerTotal += item.price * item.quantity;
                    });
                    
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
                                <div class="d-flex align-items-center">
                                    <span class="badge ${getStatusBadgeClass(order.status)} me-3">${order.status}</span>
                                    <button class="btn btn-sm btn-outline-success update-status-btn" data-id="${doc.id}" data-status="${order.status}">
                                        Update Status
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6>Customer Information</h6>
                                        <p class="mb-1"><strong>Name:</strong> ${order.buyerName}</p>
                                        <p class="mb-1"><strong>Email:</strong> ${order.buyerEmail || 'Not provided'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Shipping Information</h6>
                                        <p class="mb-1"><strong>Name:</strong> ${order.shippingDetails.name}</p>
                                        <p class="mb-1"><strong>Address:</strong> ${order.shippingDetails.address}</p>
                                        <p class="mb-1"><strong>Phone:</strong> ${order.shippingDetails.phone}</p>
                                        ${order.shippingDetails.notes ? `<p class="mb-1"><strong>Notes:</strong> ${order.shippingDetails.notes}</p>` : ''}
                                    </div>
                                </div>
                                
                                <h6>Order Items (Your Products Only)</h6>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${sellerItems.map(item => `
                                                <tr>
                                                    <td>${item.name}</td>
                                                    <td>₱${item.price.toFixed(2)}</td>
                                                    <td>${item.quantity}</td>
                                                    <td class="text-end">₱${(item.price * item.quantity).toFixed(2)}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Your Total</strong></td>
                                                <td class="text-end"><strong>₱${sellerTotal.toFixed(2)}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <strong>Note:</strong> This view only shows items from your inventory. The customer's total order may include items from other sellers.
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Add event listener for update status button
                    document.querySelector('.update-status-btn').addEventListener('click', function() {
                        const orderId = this.getAttribute('data-id');
                        const currentStatus = this.getAttribute('data-status');
                        
                        // Set current order ID for the modal
                        currentOrderId = orderId;
                        
                        // Set the current status in the dropdown
                        document.getElementById('order-status').value = currentStatus;
                        
                        // Show the modal
                        const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
                        modal.show();
                    });
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
        <?php else: ?>
        // Function to load orders
        function loadOrders(tabId) {
            const sellerId = '<?php echo $_SESSION['user_id']; ?>';
            const container = document.getElementById(`${tabId}-orders-container`);
            const loading = document.getElementById(`loading-${tabId}`);
            
            // Don't reload if already loaded
            if (!loading || loading.style.display === 'none') {
                return;
            }
            
            // Create a query to Firestore for all orders
            firebase.firestore().collection('orders')
                .orderBy('orderDate', 'desc')
                .get()
                .then(snapshot => {
                    loading.style.display = 'none';
                    
                    if (snapshot.empty) {
                        container.innerHTML = `
                            <div class="text-center py-5">
                                <i data-feather="shopping-bag" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                <h4>No orders found</h4>
                                <p class="text-muted">No orders have been placed for your products yet.</p>
                            </div>
                        `;
                        feather.replace();
                        return;
                    }
                    
                    // Filter orders to only include those with items from this seller
                    // and match the selected tab status if needed
                    const relevantOrders = [];
                    
                    snapshot.forEach(doc => {
                        const order = {
                            id: doc.id,
                            ...doc.data()
                        };
                        
                        // Check if order matches the selected status tab
                        if (tabId !== 'all') {
                            if (tabId === 'processing' && (order.status !== 'Processing' && order.status !== 'Out for Delivery')) {
                                return;
                            } else if (tabId !== 'processing' && order.status.toLowerCase() !== tabId) {
                                return;
                            }
                        }
                        
                        // Check if any items in the order belong to this seller
                        const sellerItems = order.items.filter(item => item.sellerId === sellerId);
                        
                        if (sellerItems.length > 0) {
                            // Calculate total for this seller's items only
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
                        container.innerHTML = `
                            <div class="text-center py-5">
                                <i data-feather="shopping-bag" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                                <h4>No orders found</h4>
                                <p class="text-muted">No ${tabId !== 'all' ? tabId + ' ' : ''}orders have been placed for your products yet.</p>
                            </div>
                        `;
                        feather.replace();
                        return;
                    }
                    
                    // Create table to display orders
                    const table = document.createElement('table');
                    table.className = 'table table-hover';
                    
                    const tableHead = document.createElement('thead');
                    tableHead.innerHTML = `
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    `;
                    
                    const tableBody = document.createElement('tbody');
                    
                    relevantOrders.forEach(order => {
                        const orderDate = new Date(order.orderDate.toDate()).toLocaleDateString();
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>#${order.id.substring(0, 8)}</td>
                            <td>${orderDate}</td>
                            <td>${order.buyerName}</td>
                            <td>${order.sellerItems.length}</td>
                            <td>₱${order.sellerTotal.toFixed(2)}</td>
                            <td><span class="badge ${getStatusBadgeClass(order.status)}">${order.status}</span></td>
                            <td>
                                <div class="btn-group">
                                    <a href="orders.php?id=${order.id}" class="btn btn-sm btn-outline-success">View</a>
                                    <button class="btn btn-sm btn-outline-primary update-status-btn" data-id="${order.id}" data-status="${order.status}">
                                        Update
                                    </button>
                                </div>
                            </td>
                        `;
                        
                        tableBody.appendChild(row);
                    });
                    
                    table.appendChild(tableHead);
                    table.appendChild(tableBody);
                    container.innerHTML = '';
                    container.appendChild(table);
                    
                    // Add event listeners for update status buttons
                    document.querySelectorAll('.update-status-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const orderId = this.getAttribute('data-id');
                            const currentStatus = this.getAttribute('data-status');
                            
                            // Set current order ID for the modal
                            currentOrderId = orderId;
                            
                            // Set the current status in the dropdown
                            document.getElementById('order-status').value = currentStatus;
                            
                            // Show the modal
                            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
                            modal.show();
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
        <?php endif; ?>
        
        // Event listener for update status confirmation
        document.getElementById('confirm-update-status').addEventListener('click', function() {
            if (!currentOrderId) return;
            
            const newStatus = document.getElementById('order-status').value;
            
            // Disable button to prevent multiple clicks
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
            
            // Update order status in Firestore
            firebase.firestore().collection('orders').doc(currentOrderId).update({
                status: newStatus
            })
            .then(() => {
                // Hide the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('updateStatusModal'));
                modal.hide();
                
                // Refresh the page to show updated status
                <?php if ($orderId): ?>
                    window.location.reload();
                <?php else: ?>
                    // Reset loading states for all tabs to force reload
                    document.getElementById('loading-all').style.display = 'block';
                    document.getElementById('all-orders-container').innerHTML = '';
                    
                    document.getElementById('loading-pending').style.display = 'block';
                    document.getElementById('pending-orders-container').innerHTML = '';
                    
                    document.getElementById('loading-processing').style.display = 'block';
                    document.getElementById('processing-orders-container').innerHTML = '';
                    
                    document.getElementById('loading-delivered').style.display = 'block';
                    document.getElementById('delivered-orders-container').innerHTML = '';
                    
                    document.getElementById('loading-cancelled').style.display = 'block';
                    document.getElementById('cancelled-orders-container').innerHTML = '';
                    
                    // Reload the current tab
                    const activeTab = document.querySelector('.nav-link.active').getAttribute('href').substring(1);
                    loadOrders(activeTab);
                <?php endif; ?>
            })
            .catch(error => {
                console.error("Error updating order status: ", error);
                alert('Error updating order status. Please try again later.');
                
                // Re-enable button
                this.disabled = false;
                this.textContent = 'Update Status';
            });
        });
        
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
    </script>
</body>
</html>
