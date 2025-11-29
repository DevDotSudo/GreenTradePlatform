<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';
include '../includes/functions.php';

ensureUserLoggedIn('buyer');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Green Trade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">My Shopping Cart</h1>
                <p class="page-subtitle">Review your items and proceed to checkout</p>
            </div>

            <!-- Cart Items Container -->
            <div class="cart-section">
                <div id="cart-items-container">
                    <!-- Cart items will be loaded here -->
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <div class="loading-text">Loading your cart...</div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="order-summary" id="order-summary" style="display: none;">
                    <div class="summary-card">
                        <h3 class="summary-title">Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal">₱0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery Fee</span>
                            <span id="delivery-fee">₱0.00</span>
                        </div>
                        <div class="summary-row total-row">
                            <strong>Total</strong>
                            <strong id="total">₱0.00</strong>
                        </div>
                        <button id="checkout-button" class="btn btn-success btn-lg w-100" disabled onclick="proceedToCheckout()">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Checkout Modal -->
    <div id="checkout-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div class="modal-backdrop" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5);"></div>
        <div class="modal-dialog" style="background: white; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); max-width: 500px; width: 90%; max-height: 90vh; overflow: hidden; position: relative; z-index: 10000;">
            <div class="modal-content">
                <div class="modal-header" style="padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between;">
                    <h5 class="modal-title" style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Shipping Information</h5>
                    <button type="button" class="modal-close" onclick="closeCheckoutModal()" style="background: none; border: none; font-size: 24px; color: #6b7280; cursor: pointer; padding: 4px; border-radius: 6px;">&times;</button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <form id="shipping-form">
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="name" class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Full Name *</label>
                            <input type="text" class="form-control" id="name" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="phone" class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="address" class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Delivery Address *</label>
                            <textarea class="form-control" id="address" rows="3" required style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="notes" class="form-label" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Delivery Notes (Optional)</label>
                            <textarea class="form-control" id="notes" rows="2" placeholder="Any special instructions..." style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="padding: 24px; border-top: 1px solid #e5e7eb; display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeCheckoutModal()" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirm-checkout" onclick="confirmCheckout()" style="padding: 8px 16px; background: #10b981; color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                        <span class="btn-text">Place Order</span>
                        <span class="btn-loading d-none" style="display: flex; align-items: center;">
                            <span class="spinner-border spinner-border-sm me-2" style="width: 14px; height: 14px; border: 2px solid #ffffff; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite;"></span>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>

    <!-- Core Dependencies -->
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dialogs.js"></script>

    <!-- Services -->
    <script src="../assets/js/services/cartService.js"></script>
    <script src="../assets/js/services/orderService.js"></script>

    <style>
        .main-content {
            padding: var(--space-8) 0;
            min-height: calc(100vh - 200px);
        }

        .page-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: var(--space-3);
        }

        .page-subtitle {
            font-size: 1.125rem;
            color: var(--gray-600);
            margin: 0;
        }

        .cart-section {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: var(--space-8);
            align-items: start;
        }

        .cart-items {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .cart-header {
            background: var(--gray-50);
            padding: var(--space-6);
            border-bottom: 1px solid var(--gray-200);
        }

        .cart-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }

        .cart-body {
            padding: var(--space-6);
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: var(--space-4);
            padding: var(--space-4);
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-3);
            transition: all var(--transition-fast);
        }

        .cart-item.selected {
            background: var(--primary-50);
            border: 2px solid var(--primary-200);
        }

        .cart-item.unselected {
            opacity: 0.6;
        }

        .item-checkbox {
            display: flex;
            align-items: center;
            margin-right: var(--space-2);
        }

        .item-select {
            display: none;
        }

        .checkbox-label {
            width: 20px;
            height: 20px;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-md);
            cursor: pointer;
            position: relative;
            transition: all var(--transition-fast);
            background: white;
        }

        .item-select:checked + .checkbox-label {
            background: var(--primary-500);
            border-color: var(--primary-500);
        }

        .item-select:checked + .checkbox-label::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .seller-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .seller-actions {
            display: flex;
            gap: var(--space-2);
        }

        .d-none {
            display: none !important;
        }

        .item-image {
            width: 80px;
            height: 80px;
            background: var(--gray-200);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--space-1);
        }

        .item-meta {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .item-price {
            font-weight: 600;
            color: var(--primary-600);
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid var(--gray-300);
            background: white;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .quantity-btn:hover {
            background: var(--gray-50);
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-md);
            padding: var(--space-1);
        }

        .remove-item {
            color: var(--error-500);
            cursor: pointer;
            padding: var(--space-2);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .remove-item:hover {
            background: var(--error-50);
        }

        .order-summary {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            position: sticky;
            top: var(--space-4);
        }

        .summary-card {
            padding: var(--space-6);
        }

        .summary-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: var(--space-4);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-2);
            color: var(--gray-700);
        }

        .summary-row.total-row {
            padding-top: var(--space-3);
            border-top: 2px solid var(--gray-200);
            font-size: 1.125rem;
            color: var(--gray-900);
        }

        .empty-cart {
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

        .loading-state {
            text-align: center;
            padding: var(--space-12) var(--space-6);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--gray-200);
            border-top-color: var(--primary-500);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto var(--space-4);
        }

        .loading-text {
            color: var(--gray-600);
            font-size: 1rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: var(--z-modal);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-dialog {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-2xl);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow: hidden;
            position: relative;
            z-index: var(--z-modal);
        }

        .modal-header {
            padding: var(--space-6);
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray-500);
            cursor: pointer;
            padding: var(--space-2);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
        }

        .modal-close:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .modal-body {
            padding: var(--space-6);
        }

        .modal-footer {
            padding: var(--space-6);
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: var(--space-3);
            justify-content: flex-end;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: var(--space-6) 0;
            }

            .page-title {
                font-size: 2rem;
            }

            .cart-section {
                grid-template-columns: 1fr;
                gap: var(--space-6);
            }

            .order-summary {
                position: static;
            }

            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--space-3);
            }

            .item-image {
                width: 60px;
                height: 60px;
            }
        }
    </style>

    <script>
        let cartService = null;
        let orderService = null;
        let currentCart = null;
        let selectedItems = new Set(); // Track selected item IDs

        document.addEventListener('DOMContentLoaded', function() {
            waitForFirebase(() => {
                initializeCart();
            });
        });

        async function initializeCart() {
            try {
                cartService = new CartService();
                orderService = new OrderService();

                await cartService.init();
                await orderService.init();

                await loadCart();
            } catch (error) {
                console.error('Error initializing cart:', error);
                showError('Failed to load cart. Please refresh the page.');
            }
        }

        async function loadCart() {
            try {
                const container = document.getElementById('cart-items-container');
                container.innerHTML = `
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <div class="loading-text">Loading your cart...</div>
                    </div>
                `;

                currentCart = await cartService.getUserCart();

                if (!currentCart.items || currentCart.items.length === 0) {
                    showEmptyCart();
                    return;
                }

                // Automatically clean up invalid items
                try {
                    await cartService.cleanupCart();
                    // Reload cart after cleanup
                    currentCart = await cartService.getUserCart();
                } catch (cleanupError) {
                    console.error('Error during cart cleanup:', cleanupError);
                }

                if (!currentCart.items || currentCart.items.length === 0) {
                    showEmptyCart();
                    return;
                }

                renderCartItems(currentCart);
                initializeItemSelection();
                showOrderSummary();

            } catch (error) {
                console.error('Error loading cart:', error);
                showError('Failed to load cart items. Please try again.');
            }
        }

        function renderCartItems(cart) {
            const container = document.getElementById('cart-items-container');

            // Group items by seller
            const itemsBySeller = {};
            cart.items.forEach(item => {
                const sellerId = item.sellerId || 'unknown';
                if (!itemsBySeller[sellerId]) {
                    itemsBySeller[sellerId] = {
                        sellerName: item.sellerName || 'Unknown Seller',
                        items: []
                    };
                }
                itemsBySeller[sellerId].items.push(item);
            });

            let html = '';

            Object.keys(itemsBySeller).forEach(sellerId => {
                const sellerGroup = itemsBySeller[sellerId];
                html += `
                    <div class="cart-items" data-seller-id="${sellerId}">
                        <div class="cart-header">
                            <div class="seller-header">
                                <h3 class="cart-title">From: ${escapeHtml(sellerGroup.sellerName)}</h3>
                                <div class="seller-actions">
                                    <button class="btn btn-sm btn-outline-primary select-all-btn" onclick="selectAllSellerItems('${sellerId}')">
                                        Select All
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary deselect-all-btn d-none" onclick="deselectAllSellerItems('${sellerId}')">
                                        Deselect All
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="cart-body">
                            ${sellerGroup.items.map(item => createCartItemHtml(item)).join('')}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function createCartItemHtml(item) {
            const price = Number(item.price) || 0;
            const quantity = Number(item.quantity) || 0;
            const itemTotal = price * quantity;
            const productId = String(item.productId || item.id || '').trim();
            const productName = escapeHtml(item.productName || item.name || 'Unnamed Product');
            const imageSrc = item.imageUrl || item.imageData || '../assets/svg/empty-cart.svg';

            return `
                <div class="cart-item" data-product-id="${productId}">
                    <div class="item-checkbox">
                        <input type="checkbox" class="item-select" id="select-${productId}" checked onchange="toggleItemSelection('${productId}')">
                        <label for="select-${productId}" class="checkbox-label"></label>
                    </div>
                    <div class="item-image">
                        <img src="${imageSrc}" alt="${productName}" onerror="this.src='../assets/svg/empty-cart.svg'">
                    </div>
                    <div class="item-details">
                        <div class="item-name">${productName}</div>
                        <div class="item-meta">₱${price.toFixed(2)} each</div>
                    </div>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity('${productId}', ${quantity - 1})">-</button>
                        <input type="number" class="quantity-input" value="${quantity}" min="1" onchange="updateQuantity('${productId}', this.value)">
                        <button class="quantity-btn" onclick="updateQuantity('${productId}', ${quantity + 1})">+</button>
                    </div>
                    <div class="item-price">₱${itemTotal.toFixed(2)}</div>
                    <button class="remove-item" onclick="removeItem('${productId}')" title="Remove item">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            `;
        }

        async function updateQuantity(productId, newQuantity) {
            try {
                newQuantity = parseInt(newQuantity);
                if (isNaN(newQuantity) || newQuantity < 1) {
                    newQuantity = 1;
                }

                await cartService.updateCartItem(productId, newQuantity);
                await loadCart(); // Reload to show updated cart

            } catch (error) {
                console.error('Error updating quantity:', error);
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Error',
                        message: 'Failed to update quantity. Please try again.',
                        type: 'error'
                    });
                }
            }
        }

        async function removeItem(productId) {
            // Show confirmation dialog
            const confirmed = await showConfirm({
                title: 'Remove Item',
                message: 'Are you sure you want to remove this item from your cart?',
                type: 'warning',
                confirmText: 'Remove',
                cancelText: 'Cancel'
            });

            if (!confirmed) {
                return;
            }

            // Find the cart item element and add loading state
            const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
            if (cartItem) {
                cartItem.style.opacity = '0.6';
                cartItem.style.pointerEvents = 'none';

                // Add loading spinner to the remove button
                const removeBtn = cartItem.querySelector('.remove-item');
                if (removeBtn) {
                    removeBtn.innerHTML = '<div class="loading-spinner" style="width: 16px; height: 16px; border: 2px solid #dc2626; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>';
                    removeBtn.disabled = true;
                }
            }

            try {
                await cartService.removeFromCart(productId);
                await loadCart(); // Reload to show updated cart

                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Item Removed',
                        message: 'Item has been removed from your cart.',
                        type: 'success'
                    });
                }

            } catch (error) {
                console.error('Error removing item:', error);

                // Reset loading state on error
                if (cartItem) {
                    cartItem.style.opacity = '1';
                    cartItem.style.pointerEvents = 'auto';

                    const removeBtn = cartItem.querySelector('.remove-item');
                    if (removeBtn) {
                        removeBtn.innerHTML = '<i data-feather="trash-2"></i>';
                        removeBtn.disabled = false;
                        feather.replace(); // Reinitialize icons
                    }
                }

                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Error',
                        message: 'Failed to remove item. Please try again.',
                        type: 'error'
                    });
                }
            }
        }

        function initializeItemSelection() {
            // Initialize all items as selected by default
            selectedItems.clear();
            if (currentCart && currentCart.items) {
                currentCart.items.forEach(item => {
                    const productId = String(item.productId || item.id || '').trim();
                    selectedItems.add(productId);
                });
            }
            updateSellerSelectionButtons();
        }

        function toggleItemSelection(productId) {
            const checkbox = document.getElementById(`select-${productId}`);
            const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);

            if (checkbox.checked) {
                selectedItems.add(productId);
                cartItem.classList.remove('unselected');
                cartItem.classList.add('selected');
            } else {
                selectedItems.delete(productId);
                cartItem.classList.remove('selected');
                cartItem.classList.add('unselected');
            }

            showOrderSummary();
            updateSellerSelectionButtons();
        }

        function selectAllSellerItems(sellerId) {
            const sellerSection = document.querySelector(`.cart-items[data-seller-id="${sellerId}"]`);
            const checkboxes = sellerSection.querySelectorAll('.item-select');

            checkboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.checked = true;
                    const productId = checkbox.id.replace('select-', '');
                    selectedItems.add(productId);
                    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                    cartItem.classList.remove('unselected');
                    cartItem.classList.add('selected');
                }
            });

            showOrderSummary();
            updateSellerSelectionButtons();
        }

        function deselectAllSellerItems(sellerId) {
            const sellerSection = document.querySelector(`.cart-items[data-seller-id="${sellerId}"]`);
            const checkboxes = sellerSection.querySelectorAll('.item-select');

            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    checkbox.checked = false;
                    const productId = checkbox.id.replace('select-', '');
                    selectedItems.delete(productId);
                    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                    cartItem.classList.remove('selected');
                    cartItem.classList.add('unselected');
                }
            });

            updateOrderSummary();
            updateSellerSelectionButtons();
        }

        function updateSellerSelectionButtons() {
            // Update select all/deselect all buttons for each seller
            document.querySelectorAll('.cart-items').forEach(sellerSection => {
                const sellerId = sellerSection.dataset.sellerId;
                const checkboxes = sellerSection.querySelectorAll('.item-select');
                const selectAllBtn = sellerSection.querySelector('.select-all-btn');
                const deselectAllBtn = sellerSection.querySelector('.deselect-all-btn');

                const checkedCount = sellerSection.querySelectorAll('.item-select:checked').length;
                const totalCount = checkboxes.length;

                if (checkedCount === totalCount) {
                    // All selected - show deselect all
                    selectAllBtn.classList.add('d-none');
                    deselectAllBtn.classList.remove('d-none');
                } else {
                    // Not all selected - show select all
                    selectAllBtn.classList.remove('d-none');
                    deselectAllBtn.classList.add('d-none');
                }
            });
        }


        function showEmptyCart() {
            const container = document.getElementById('cart-items-container');
            container.innerHTML = `
                <div class="empty-cart">
                    <div class="empty-icon">🛒</div>
                    <h3 class="empty-title">Your cart is empty</h3>
                    <p class="empty-description">Browse our products and add items to your cart.</p>
                    <a href="products.php" class="btn btn-success">Browse Products</a>
                </div>
            `;
            document.getElementById('order-summary').style.display = 'none';
        }

        function showOrderSummary() {
            const summaryEl = document.getElementById('order-summary');
            const subtotalEl = document.getElementById('subtotal');
            const deliveryFeeEl = document.getElementById('delivery-fee');
            const totalEl = document.getElementById('total');
            const checkoutBtn = document.getElementById('checkout-button');

            if (!currentCart || !currentCart.items || currentCart.items.length === 0) {
                summaryEl.style.display = 'none';
                return;
            }

            summaryEl.style.display = 'block';

            // Calculate subtotal only for selected items
            const subtotal = currentCart.items
                .filter(item => {
                    const productId = String(item.productId || item.id || '').trim();
                    return selectedItems.has(productId);
                })
                .reduce((total, item) => total + (item.price * item.quantity), 0);

            const deliveryFee = subtotal > 0 ? 50 : 0; // Only add delivery fee if there are selected items
            const total = subtotal + deliveryFee;

            subtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
            deliveryFeeEl.textContent = `₱${deliveryFee.toFixed(2)}`;
            totalEl.textContent = `₱${total.toFixed(2)}`;

            checkoutBtn.disabled = subtotal <= 0;
        }

        function showError(message) {
            const container = document.getElementById('cart-items-container');
            container.innerHTML = `
                <div class="empty-cart">
                    <div class="empty-icon">⚠️</div>
                    <h3 class="empty-title">Error</h3>
                    <p class="empty-description">${escapeHtml(message)}</p>
                    <button class="btn btn-primary" onclick="loadCart()">Try Again</button>
                </div>
            `;
            document.getElementById('order-summary').style.display = 'none';
        }

        function escapeHtml(str) {
            if (typeof str !== 'string') return '';
            const map = {
                '&': '&',
                '<': '<',
                '>': '>',
                '"': '"',
                "'": '&#039;'
            };
            return str.replace(/[&<>"']/g, m => map[m]);
        }

        // Simple checkout function
        function proceedToCheckout() {
            const checkoutBtn = document.getElementById('checkout-button');

            // Check if button is disabled
            if (checkoutBtn.disabled) {
                return;
            }

            // Check if any items are selected
            if (selectedItems.size === 0) {
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'No Items Selected',
                        message: 'Please select at least one item to proceed to checkout.',
                        type: 'warning'
                    });
                }
                return;
            }

            // Open checkout modal
            const modal = document.getElementById('checkout-modal');
            if (modal) {
                modal.style.display = 'flex';
            } else {
                console.error('Checkout modal not found!');
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Error',
                        message: 'Checkout modal not available. Please refresh the page.',
                        type: 'error'
                    });
                }
            }
        }

        function closeCheckoutModal() {
            const modal = document.getElementById('checkout-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        async function confirmCheckout() {
            const confirmBtn = document.getElementById('confirm-checkout');
            const btnText = confirmBtn.querySelector('.btn-text');
            const btnLoading = confirmBtn.querySelector('.btn-loading');

            // Validate form
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();

            if (!name || !phone || !address) {
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Validation Error',
                        message: 'Please fill in all required fields.',
                        type: 'warning'
                    });
                }
                return;
            }

            // Show loading state
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            confirmBtn.disabled = true;

            try {
                // Validate cart items before checkout
                const validation = await cartService.validateCartItems();
                console.log('Cart validation result:', validation);

                if (!validation.isValid && validation.invalidItems && validation.invalidItems.length > 0) {
                    console.log('Invalid items found:', validation.invalidItems);
                    
                    let message = 'Some items in your cart need attention:\n\n';
                    validation.invalidItems.forEach(item => {
                        const productName = item.productName || item.name || 'Product';
                        message += `• ${productName}: ${item.reason}\n`;
                    });
                    message += '\nWould you like to remove these items and proceed with the valid ones?';

                    if (typeof showConfirm === 'function') {
                        const proceed = await showConfirm({
                            title: 'Cart Validation',
                            message: message,
                            confirmText: 'Remove Invalid Items',
                            cancelText: 'Cancel'
                        });

                        if (!proceed) {
                            closeCheckoutModal();
                            return;
                        }

                        // Remove invalid items
                        for (const item of validation.invalidItems) {
                            console.log('Removing invalid item:', item.productId);
                            await cartService.removeFromCart(item.productId);
                        }

                        // Reload cart
                        await loadCart();

                        if (!currentCart || !currentCart.items || currentCart.items.length === 0) {
                            closeCheckoutModal();
                            return;
                        }
                    }
                } else {
                    console.log('Cart validation passed - all items are valid');
                }

                // Create order with only selected items
                const selectedCartItems = currentCart.items.filter(item => {
                    const productId = String(item.productId || item.id || '').trim();
                    return selectedItems.has(productId);
                });

                if (selectedCartItems.length === 0) {
                    if (typeof showToast === 'function') {
                        showToast({
                            title: 'No Items Selected',
                            message: 'Please select at least one item to checkout.',
                            type: 'warning'
                        });
                    }
                    closeCheckoutModal();
                    return;
                }

                const shippingInfo = {
                    name: name,
                    phone: phone,
                    address: address,
                    notes: document.getElementById('notes').value.trim()
                };

                const order = await orderService.createOrder(selectedCartItems, shippingInfo);

                // Clear cart after successful order
                await cartService.clearCart();

                // Show success message and redirect
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Order Placed!',
                        message: `Your order #${order.id.substring(0, 8)} has been placed successfully.`,
                        type: 'success'
                    });
                }

                // Close modal and redirect
                closeCheckoutModal();
                setTimeout(() => {
                    window.location.href = 'orders.php';
                }, 1500);

            } catch (error) {
                console.error('Error during checkout:', error);
                if (typeof showToast === 'function') {
                    showToast({
                        title: 'Checkout Failed',
                        message: error.message || 'Failed to place order. Please try again.',
                        type: 'error'
                    });
                }
            } finally {
                // Reset button state
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
                confirmBtn.disabled = false;
            }
        }

        // Initialize Feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    </script>
</body>

</html>