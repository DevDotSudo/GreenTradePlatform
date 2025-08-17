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
                        <button id="checkout-button" class="btn btn-success w-100" disabled>Proceed to Checkout</button>
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
        // Global variables
        let currentCartId = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();

            waitForFirebase(() => {
                loadCart();

                // Checkout button click handler
                document.getElementById('checkout-button').addEventListener('click', function() {
                    window.location.href = 'checkout.php';
                });
            });
        });

        function loadCart() {
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            const cartItemsContainer = document.getElementById('cart-items-container');
            const loading = document.getElementById('loading');
            const emptyCart = document.getElementById('empty-cart');
            const checkoutButton = document.getElementById('checkout-button');

            loading.style.display = 'block';
            emptyCart.classList.add('d-none');
            checkoutButton.disabled = true;

            firebase.firestore().collection('carts')
                .where('userId', '==', userId)
                .limit(1)
                .get()
                .then(snapshot => {
                    loading.style.display = 'none';

                    if (snapshot.empty) {
                        showEmptyCart();
                        return;
                    }

                    const cartDoc = snapshot.docs[0];
                    currentCartId = cartDoc.id;
                    const cart = cartDoc.data();

                    if (!cart || Object.keys(cart).length === 0) {
                        showEmptyCart();
                        return;
                    }

                    renderCartItems(cartDoc.id, [cart]);
                })
                .catch(error => {
                    loading.style.display = 'none';
                    console.error("Error getting cart: ", error);
                    showError("Error loading cart. Please try again later.");
                });
        }

        function renderCartItems(cartId, items) {
            const cartItemsContainer = document.getElementById('cart-items-container');
            const checkoutButton = document.getElementById('checkout-button');

            cartItemsContainer.innerHTML = '';

            const itemsBySeller = {};
            let subtotal = 0;

            items.forEach(item => {
                if (!itemsBySeller[item.sellerId]) {
                    itemsBySeller[item.sellerId] = {
                        sellerName: item.sellerName,
                        items: []
                    };
                }

                itemsBySeller[item.sellerId].items.push(item);
                subtotal += item.price * item.quantity;
            });

            Object.keys(itemsBySeller).forEach(sellerId => {
                const sellerGroup = itemsBySeller[sellerId];

                const sellerSection = document.createElement('div');
                sellerSection.className = 'mb-4';
                sellerSection.innerHTML = `
            <h5 class="mb-3">Seller: ${sellerGroup.sellerName}</h5>
            <table class="table align-middle">
                <tbody>
                    ${sellerGroup.items.map(item => {
                        const itemTotal = item.price * item.quantity;
                        return `
                            <tr>
                                <td width="80">
                                    <div class="bg-light rounded text-center p-2" style="width: 60px; height: 60px;">
                                        <img src="${item.imageUrl || '../assets/svg/package.svg'}" 
                                             alt="${item.productName || 'No Name'}" 
                                             style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    </div>
                                </td>
                                <td>
                                    <h6 class="mb-0">${item.productName || 'Unnamed Product'}</h6>
                                    <p class="text-muted mb-0">₱${item.price.toFixed(2)} x ${item.quantity}</p>
                                </td>
                                <td class="text-end">
                                    <h6 class="mb-0">₱${itemTotal.toFixed(2)}</h6>
                                    <button class="btn btn-sm btn-link text-danger remove-item" 
                                        data-product-id="${item.productId}">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;

                cartItemsContainer.appendChild(sellerSection);
            });

            updateOrderSummary(subtotal);

            checkoutButton.disabled = false;

            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    removeFromCart(currentCartId, productId);
                });
            });

            feather.replace();
        }

        function updateOrderSummary(subtotal) {
            const deliveryFee = 50; // Fixed delivery fee
            const total = subtotal + deliveryFee;

            document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
            document.getElementById('delivery-fee').textContent = `₱${deliveryFee.toFixed(2)}`;
            document.getElementById('total').textContent = `₱${total.toFixed(2)}`;
        }

        function removeFromCart(cartId, productId) {
            if (!cartId) return;

            const button = document.querySelector(`.remove-item[data-product-id="${productId}"]`);
            if (button) button.disabled = true;

            firebase.firestore().collection('carts').doc(cartId).get()
                .then(doc => {
                    if (!doc.exists) {
                        throw new Error('Cart not found');
                    }

                    const cart = doc.data();
                    const updatedItems = cart.items.filter(item => item.productId !== productId);

                    return firebase.firestore().collection('carts').doc(cartId).update({
                        items: updatedItems
                    });
                })
                .then(() => {
                    // Reload cart
                    loadCart();

                    // Update cart badge
                    if (typeof updateCartBadge === 'function') {
                        updateCartBadge();
                    }
                })
                .catch(error => {
                    console.error("Error removing item from cart: ", error);
                    showError("Error removing item. Please try again later.");
                    if (button) button.disabled = false;
                });
        }

        function showEmptyCart() {
            document.getElementById('empty-cart').classList.remove('d-none');
            document.getElementById('checkout-button').disabled = true;
            updateOrderSummary(0);
        }

        function showError(message) {
            const cartItemsContainer = document.getElementById('cart-items-container');
            cartItemsContainer.innerHTML = `
                <div class="alert alert-danger">
                    ${message}
                </div>
            `;
        }
    </script>
</body>

</html>