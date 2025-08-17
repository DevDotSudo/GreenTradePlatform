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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Order Details</h1>
                <a href="orders.php" class="btn btn-outline-success">
                    <i data-feather="arrow-left" class="me-1"></i> Back to Orders
                </a>
            </div>

            <div id="order-details-container">
                <div class="text-center py-5" id="loading">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading order details...</p>
                </div>
            </div>
        <?php else: ?>
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
        // Global variable to track current order being modified
        let currentOrderId = null;

        // Helper function to get status badge class
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

        // Function to load order details
        function loadOrderDetails(orderId) {
            waitForFirebase(() => {
                const sellerId = '<?php echo $_SESSION['user_id']; ?>';
                const orderDetailsContainer = document.getElementById('order-details-container');
                const loading = document.getElementById('loading');

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
                        const sellerItems = order.items.filter(item => item.sellerId === sellerId);

                        if (sellerItems.length === 0) {
                            orderDetailsContainer.innerHTML = `
                                <div class="alert alert-danger">
                                    This order does not contain any items from your inventory.
                                </div>
                            `;
                            return;
                        }

                        let sellerTotal = 0;
                        sellerItems.forEach(item => {
                            sellerTotal += item.price * item.quantity;
                        });

                        const orderDate = new Date(order.orderDate.toDate()).toLocaleString();
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
                            currentOrderId = this.getAttribute('data-id');
                            document.getElementById('order-status').value = this.getAttribute('data-status');
                            new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
                        });
                    })
                    .catch(error => {
                        loading.style.display = 'none';
                        orderDetailsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                Error loading order details: ${error.message}
                            </div>
                        `;
                    });
            });
        }

        // Function to load orders for a specific tab
        function loadOrders(tabId) {
            waitForFirebase(() => {
                const sellerId = '<?php echo $_SESSION['user_id']; ?>';
                const container = document.getElementById(`${tabId}-orders-container`);
                const loading = document.getElementById(`loading-${tabId}`);

                if (!loading || loading.style.display === 'none') return;

                firebase.firestore().collection('orders')
                    .orderBy('orderDate', 'desc')
                    .get()
                    .then(snapshot => {
                        loading.style.display = 'none';

                        if (snapshot.empty) {
                            container.innerHTML = noOrdersMessage(tabId);
                            return;
                        }

                        const relevantOrders = [];
                        snapshot.forEach(doc => {
                            const order = {
                                id: doc.id,
                                ...doc.data()
                            };

                            // Filter by status if not 'all' tab
                            if (tabId !== 'all') {
                                if (tabId === 'processing' && !['Processing', 'Out for Delivery'].includes(order.status)) {
                                    return;
                                } else if (tabId !== 'processing' && order.status.toLowerCase() !== tabId) {
                                    return;
                                }
                            }

                            // Filter items by seller
                            const sellerItems = order.items.filter(item => item.sellerId === sellerId);
                            if (sellerItems.length > 0) {
                                const sellerTotal = sellerItems.reduce((total, item) => total + (item.price * item.quantity), 0);
                                order.sellerItems = sellerItems;
                                order.sellerTotal = sellerTotal;
                                relevantOrders.push(order);
                            }
                        });

                        if (relevantOrders.length === 0) {
                            container.innerHTML = noOrdersMessage(tabId);
                            return;
                        }

                        // Create orders table
                        container.innerHTML = `
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${relevantOrders.map(order => {
                                        const orderDate = new Date(order.orderDate.toDate()).toLocaleDateString();
                                        return `
                                            <tr>
                                                <td>#${order.id.substring(0, 8)}</td>
                                                <td>${orderDate}</td>
                                                <td>${order.buyerName}</td>
                                                <td>${order.sellerItems.length}</td>
                                                <td>₱${order.sellerTotal.toFixed(2)}</td>
                                                <td><span class="badge ${getStatusBadgeClass(order.status)}">${order.status}</span></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="orders.php?id=${order.id}" class="btn btn-sm btn-outline-success">View</a>
                                                        <button class="btn btn-sm btn-outline-primary update-status-btn" 
                                                            data-id="${order.id}" data-status="${order.status}">
                                                            Update
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        `;

                        // Add event listeners for update status buttons
                        document.querySelectorAll('.update-status-btn').forEach(button => {
                            button.addEventListener('click', function() {
                                currentOrderId = this.getAttribute('data-id');
                                document.getElementById('order-status').value = this.getAttribute('data-status');
                                new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
                            });
                        });
                    })
                    .catch(error => {
                        loading.style.display = 'none';
                        container.innerHTML = `
                            <div class="alert alert-danger">
                                Error loading orders: ${error.message}
                            </div>
                        `;
                    });
            });
        }

        // Helper function for no orders message
        function noOrdersMessage(tabId) {
            return `
                <div class="text-center py-5">
                    <i data-feather="shopping-bag" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                    <h4>No orders found</h4>
                    <p class="text-muted">No ${tabId !== 'all' ? tabId + ' ' : ''}orders have been placed for your products yet.</p>
                </div>
            `;
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();

            waitForFirebase(() => {
                <?php if ($orderId): ?>
                    loadOrderDetails('<?php echo $orderId; ?>');
                <?php else: ?>
                    loadOrders('all');
                    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
                        tab.addEventListener('shown.bs.tab', function(e) {
                            const targetId = e.target.getAttribute('href').substring(1);
                            loadOrders(targetId);
                        });
                    });
                <?php endif; ?>
            });

            // Status update handler
            document.getElementById('confirm-update-status').addEventListener('click', function() {
                if (!currentOrderId) return;

                const button = this;
                const newStatus = document.getElementById('order-status').value;

                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

                waitForFirebase(() => {
                    firebase.firestore().collection('orders').doc(currentOrderId).update({
                            status: newStatus
                        })
                        .then(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('updateStatusModal'));
                            modal.hide();

                            <?php if ($orderId): ?>
                                window.location.reload();
                            <?php else: ?>
                                    // Reset loading states
                                    ['all', 'pending', 'processing', 'delivered', 'cancelled'].forEach(tab => {
                                        const loading = document.getElementById(`loading-${tab}`);
                                        if (loading) loading.style.display = 'block';
                                        const container = document.getElementById(`${tab}-orders-container`);
                                        if (container) container.innerHTML = '';
                                    });

                                // Reload current tab
                                const activeTab = document.querySelector('.nav-link.active').getAttribute('href').substring(1);
                                loadOrders(activeTab);
                            <?php endif; ?>
                        })
                        .catch(error => {
                            console.error("Error updating order status:", error);
                            alert('Failed to update status: ' + error.message);
                        })
                        .finally(() => {
                            button.disabled = false;
                            button.textContent = 'Update Status';
                        });
                });
            });
        });
    </script>
</body>

</html>