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
    <title>My Cart - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Cart</h1>
            <a href="products.php" class="btn btn-outline-success">
                <i data-feather="arrow-left" class="me-1"></i> Continue Shopping
            </a>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <div id="cart-items-container">
                            <!-- Cart items will be loaded here -->
                            <div class="text-center py-5" id="loading">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading cart items...</p>
                            </div>
                            
                            <div class="text-center py-5 d-none" id="empty-cart">
                                <img src="../assets/svg/empty-cart.svg" alt="Empty Cart" style="max-width: 150px;" class="mb-3">
                                <h4>Your cart is empty</h4>
                                <p class="text-muted">Browse our products and add items to your cart.</p>
                                <a href="products.php" class="btn btn-success mt-2">Browse Products</a>
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
                        <button id="checkout-button" class="btn btn-success w-100">Proceed to Checkout</button>
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
    <script src="../assets/js/cart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();
            
            // Load cart items
            loadCart();
            
            // Checkout button click handler
            document.getElementById('checkout-button').addEventListener('click', function() {
                window.location.href = 'checkout.php';
            });
        });
        
        // Function to load cart items
        function loadCart() {
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            const cartItemsContainer = document.getElementById('cart-items-container');
            const loading = document.getElementById('loading');
            const emptyCart = document.getElementById('empty-cart');
            const checkoutButton = document.getElementById('checkout-button');
            
            // Get cart from Firestore
            firebase.firestore().collection('carts')
                .where('userId', '==', userId)
                .limit(1)
                .get()
                .then(snapshot => {
                    loading.style.display = 'none';
                    
                    if (snapshot.empty) {
                        emptyCart.classList.remove('d-none');
                        checkoutButton.disabled = true;
                        updateOrderSummary(0);
                        return;
                    }
                    
                    // Get the first (and presumably only) cart document
                    const cartDoc = snapshot.docs[0];
                    const cart = cartDoc.data();
                    
                    // Check if cart has items
                    if (!cart.items || cart.items.length === 0) {
                        emptyCart.classList.remove('d-none');
                        checkoutButton.disabled = true;
                        updateOrderSummary(0);
                        return;
                    }
                    
                    // Clear container
                    cartItemsContainer.innerHTML = '';
                    
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
                    
                    // Create and append cart item elements grouped by seller
                    let subtotal = 0;
                    
                    Object.keys(itemsBySeller).forEach(sellerId => {
                        const sellerGroup = itemsBySeller[sellerId];
                        
                        // Create seller section
                        const sellerSection = document.createElement('div');
                        sellerSection.className = 'mb-3';
                        sellerSection.innerHTML = `
                            <h5 class="mb-3">Seller: ${sellerGroup.sellerName}</h5>
                        `;
                        
                        // Create items table
                        const itemsTable = document.createElement('table');
                        itemsTable.className = 'table align-middle';
                        
                        const tableBody = document.createElement('tbody');
                        
                        sellerGroup.items.forEach(item => {
                            const itemTotal = item.price * item.quantity;
                            subtotal += itemTotal;
                            
                            const tableRow = document.createElement('tr');
                            tableRow.innerHTML = `
                                <td width="80">
                                    <div class="bg-light rounded text-center p-2" style="width: 60px; height: 60px;">
                                        <i data-feather="package" style="width: 30px; height: 30px;"></i>
                                    </div>
                                </td>
                                <td>
                                    <h6 class="mb-0">${item.name}</h6>
                                    <p class="text-muted mb-0">₱${item.price.toFixed(2)} x ${item.quantity}</p>
                                </td>
                                <td class="text-end">
                                    <h6 class="mb-0">₱${itemTotal.toFixed(2)}</h6>
                                    <button class="btn btn-sm btn-link text-danger remove-item" 
                                        data-cart-id="${cartDoc.id}" 
                                        data-product-id="${item.productId}">
                                        Remove
                                    </button>
                                </td>
                            `;
                            
                            tableBody.appendChild(tableRow);
                        });
                        
                        itemsTable.appendChild(tableBody);
                        sellerSection.appendChild(itemsTable);
                        cartItemsContainer.appendChild(sellerSection);
                    });
                    
                    // Update order summary
                    updateOrderSummary(subtotal);
                    
                    // Enable checkout button if cart has items
                    checkoutButton.disabled = false;
                    
                    // Add event listeners for remove buttons
                    document.querySelectorAll('.remove-item').forEach(button => {
                        button.addEventListener('click', function() {
                            const cartId = this.getAttribute('data-cart-id');
                            const productId = this.getAttribute('data-product-id');
                            
                            removeFromCart(cartId, productId);
                        });
                    });
                    
                    // Initialize Feather icons for dynamically added content
                    feather.replace();
                })
                .catch(error => {
                    loading.style.display = 'none';
                    console.error("Error getting cart: ", error);
                    alert("Error loading cart. Please try again later.");
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
        
        // Function to remove item from cart
        function removeFromCart(cartId, productId) {
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            
            // Get the cart document
            firebase.firestore().collection('carts').doc(cartId).get()
                .then(doc => {
                    if (!doc.exists) {
                        throw new Error('Cart not found');
                    }
                    
                    const cart = doc.data();
                    
                    // Filter out the item to remove
                    const updatedItems = cart.items.filter(item => item.productId !== productId);
                    
                    // Update the cart document
                    return firebase.firestore().collection('carts').doc(cartId).update({
                        items: updatedItems
                    });
                })
                .then(() => {
                    // Reload the cart
                    loadCart();
                    
                    // Update cart badge
                    updateCartBadge();
                })
                .catch(error => {
                    console.error("Error removing item from cart: ", error);
                    alert("Error removing item. Please try again later.");
                });
        }
    </script>
</body>
</html>
