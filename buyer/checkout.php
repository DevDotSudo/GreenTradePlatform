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
    <title>Checkout - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="mb-4">
            <a href="cart.php" class="btn btn-outline-success">
                <i data-feather="arrow-left" class="me-1"></i> Back to Cart
            </a>
        </div>
        
        <h1 class="mb-4">Checkout</h1>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="shipping-form">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['phone']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($_SESSION['address']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Delivery Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Special instructions for delivery"></textarea>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment-method" id="cod" value="cod" checked>
                            <label class="form-check-label" for="cod">
                                Cash on Delivery (COD)
                            </label>
                            <p class="text-muted ms-4 mb-0 small">Pay when you receive your order</p>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div id="checkout-items-container">
                            <!-- Cart items will be loaded here -->
                            <div class="text-center py-3" id="loading">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading items...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="subtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee</span>
                            <span id="delivery-fee">₱50.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong id="total">₱50.00</strong>
                        </div>
                        <button id="place-order-button" class="btn btn-success w-100">Place Order</button>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();
            
            // Load cart items for checkout
            loadCheckoutItems();
            
            // Place order button click handler
            document.getElementById('place-order-button').addEventListener('click', function() {
                placeOrder();
            });
        });
        
        // Global variables
        let cartItems = [];
        let cartId = '';
        let subtotalAmount = 0;
        
        // Function to load checkout items
        function loadCheckoutItems() {
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            const checkoutItemsContainer = document.getElementById('checkout-items-container');
            const loading = document.getElementById('loading');
            const placeOrderButton = document.getElementById('place-order-button');
            
            // Get cart from Firestore
            firebase.firestore().collection('carts')
                .where('userId', '==', userId)
                .limit(1)
                .get()
                .then(snapshot => {
                    loading.style.display = 'none';
                    
                    if (snapshot.empty) {
                        checkoutItemsContainer.innerHTML = `
                            <div class="alert alert-warning">
                                Your cart is empty. Please add items to your cart before checkout.
                            </div>
                        `;
                        placeOrderButton.disabled = true;
                        return;
                    }
                    
                    // Get the first (and presumably only) cart document
                    const cartDoc = snapshot.docs[0];
                    cartId = cartDoc.id;
                    const cart = cartDoc.data();
                    
                    // Check if cart has items
                    if (!cart.items || cart.items.length === 0) {
                        checkoutItemsContainer.innerHTML = `
                            <div class="alert alert-warning">
                                Your cart is empty. Please add items to your cart before checkout.
                            </div>
                        `;
                        placeOrderButton.disabled = true;
                        return;
                    }
                    
                    // Store cart items for order placement
                    cartItems = cart.items;
                    
                    // Group items by seller
                    const itemsBySeller = {};
                    
                    cart.items.forEach(item => {
                        if (!itemsBySeller[item.sellerId]) {
                            itemsBySeller[item.sellerId] = {
                                sellerName: item.sellerName,
                                items: []
                            };
                        }
                        
                        itemsBySeller[item.sellerId].items.push(item);
                    });
                    
                    // Create and append checkout item elements grouped by seller
                    subtotalAmount = 0;
                    checkoutItemsContainer.innerHTML = '';
                    
                    Object.keys(itemsBySeller).forEach(sellerId => {
                        const sellerGroup = itemsBySeller[sellerId];
                        
                        // Create seller section
                        const sellerSection = document.createElement('div');
                        sellerSection.className = 'mb-3';
                        sellerSection.innerHTML = `
                            <h6 class="mb-3">From Seller: ${sellerGroup.sellerName}</h6>
                        `;
                        
                        // Create items table
                        const itemsTable = document.createElement('table');
                        itemsTable.className = 'table table-sm align-middle';
                        
                        const tableBody = document.createElement('tbody');
                        
                        sellerGroup.items.forEach(item => {
                            const itemTotal = item.price * item.quantity;
                            subtotalAmount += itemTotal;
                            
                            const tableRow = document.createElement('tr');
                            tableRow.innerHTML = `
                                <td width="60">
                                    <div class="bg-light rounded text-center p-2" style="width: 40px; height: 40px;">
                                        <i data-feather="package" style="width: 20px; height: 20px;"></i>
                                    </div>
                                </td>
                                <td>
                                    <h6 class="mb-0">${item.name}</h6>
                                    <p class="text-muted mb-0 small">₱${item.price.toFixed(2)} x ${item.quantity}</p>
                                </td>
                                <td class="text-end">₱${itemTotal.toFixed(2)}</td>
                            `;
                            
                            tableBody.appendChild(tableRow);
                        });
                        
                        itemsTable.appendChild(tableBody);
                        sellerSection.appendChild(itemsTable);
                        checkoutItemsContainer.appendChild(sellerSection);
                    });
                    
                    // Update order summary
                    updateOrderSummary(subtotalAmount);
                    
                    // Initialize Feather icons for dynamically added content
                    feather.replace();
                })
                .catch(error => {
                    loading.style.display = 'none';
                    console.error("Error getting cart: ", error);
                    
                    checkoutItemsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            Error loading cart items. Please try again later.
                        </div>
                    `;
                    
                    placeOrderButton.disabled = true;
                });
        }
        
        // Function to update order summary
        function updateOrderSummary(subtotal) {
            const deliveryFee = 50; // Fixed delivery fee
            const total = subtotal + deliveryFee;
            
            document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
            document.getElementById('delivery-fee').textContent = `₱${deliveryFee.toFixed(2)}`;
            document.getElementById('total').textContent = `₱${total.toFixed(2)}`;
        }
        
        // Function to place order
        function placeOrder() {
            // Check if cart is empty
            if (cartItems.length === 0) {
                alert('Your cart is empty. Please add items to your cart before placing an order.');
                return;
            }
            
            // Get shipping information
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            const notes = document.getElementById('notes').value.trim();
            
            // Validate shipping information
            if (!name || !phone || !address) {
                alert('Please fill in all required shipping information.');
                return;
            }
            
            // Disable place order button to prevent multiple submissions
            const placeOrderButton = document.getElementById('place-order-button');
            placeOrderButton.disabled = true;
            placeOrderButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            // Create order object
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            const deliveryFee = 50; // Fixed delivery fee
            const totalAmount = subtotalAmount + deliveryFee;
            
            const order = {
                buyerId: userId,
                buyerName: '<?php echo htmlspecialchars($_SESSION['name']); ?>',
                items: cartItems,
                shippingDetails: {
                    name: name,
                    phone: phone,
                    address: address,
                    notes: notes
                },
                orderDate: firebase.firestore.FieldValue.serverTimestamp(),
                status: 'Pending',
                paymentMethod: 'Cash on Delivery',
                subtotal: subtotalAmount,
                deliveryFee: deliveryFee,
                totalAmount: totalAmount
            };
            
            // Save order to Firestore
            firebase.firestore().collection('orders').add(order)
                .then(docRef => {
                    // Clear cart after successful order placement
                    return firebase.firestore().collection('carts').doc(cartId).update({
                        items: []
                    });
                })
                .then(() => {
                    // Redirect to order confirmation page
                    window.location.href = 'orders.php';
                })
                .catch(error => {
                    console.error("Error placing order: ", error);
                    alert('Error placing order. Please try again later.');
                    
                    // Re-enable place order button
                    placeOrderButton.disabled = false;
                    placeOrderButton.innerHTML = 'Place Order';
                });
        }
    </script>
</body>
</html>
