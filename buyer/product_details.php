<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';
include '../includes/functions.php';

ensureUserLoggedIn('buyer');

$productId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

if (!$productId) {
    header('Location: /buyer/products.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .product-image {
            max-height: 450px;
            object-fit: cover;
            border-radius: var(--radius-2xl);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: transform var(--transition-normal);
        }

        .product-image:hover {
            transform: scale(1.02);
        }

        .product-card {
            border: none;
            border-radius: var(--radius-2xl);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            background: linear-gradient(135deg, #ffffff 0%, var(--neutral-50) 100%);
            border: 1px solid var(--neutral-200);
            overflow: hidden;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            background: var(--neutral-50);
            border-radius: var(--radius-full);
            padding: 8px;
            border: 2px solid var(--neutral-200);
            transition: all var(--transition-fast);
        }

        .quantity-control:focus-within {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        .quantity-btn {
            width: 44px;
            height: 44px;
            border: none;
            background: var(--primary-500);
            color: white;
            border-radius: 50%;
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
        }

        .quantity-btn:hover:not(:disabled) {
            background: var(--primary-600);
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
        }

        .quantity-btn:disabled {
            background: var(--neutral-400);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .quantity-input {
            width: 70px;
            border: none;
            background: transparent;
            text-align: center;
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--neutral-900);
        }

        .quantity-input:focus {
            outline: none;
        }

        .add-to-cart-btn {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            border: none;
            border-radius: var(--radius-full);
            padding: 16px 32px;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all var(--transition-fast);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
            color: white;
        }

        .add-to-cart-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(34, 197, 94, 0.4);
        }

        .add-to-cart-btn:disabled {
            background: var(--neutral-400);
            transform: none;
            box-shadow: none;
            cursor: not-allowed;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: var(--z-modal);
        }

        .spinner {
            width: 56px;
            height: 56px;
            border: 4px solid var(--neutral-200);
            border-top: 4px solid var(--primary-500);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .alert-success {
            border-radius: 15px;
            border: none;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }

        .availability-info {
            background: var(--neutral-50);
            padding: var(--space-3);
            border-radius: var(--radius-lg);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-2);
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 500;
            color: var(--neutral-700);
        }

        .info-value {
            color: var(--neutral-900);
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner"></div>
            <p class="text-white mt-3 fs-5">Loading...</p>
        </div>
    </div>

    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/buyer/products.php" class="text-decoration-none">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">Product Details</li>
            </ol>
        </nav>

        <div class="product-card p-4">
            <div id="productContent">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="text-center">
                            <img id="productImage" src="" alt="" class="img-fluid product-image">
                            <div id="noImage" class="d-none">
                                <div class="bg-light rounded p-5 text-center">
                                    <i data-feather="package" style="width: 80px; height: 80px; color: #6c757d;"></i>
                                    <p class="mt-3 text-muted">No image available</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div id="productInfo">
                            <div id="productCategory" class="mb-3"></div>
                            <h1 id="productName" class="display-5 fw-bold mb-4"></h1>
                            <div id="productPrice" class="display-4 text-success fw-bold mb-4"></div>
                            <p id="productDescription" class="lead mb-4"></p>
                            
                            <div class="mb-4">
                                <h5 class="fw-bold">Availability</h5>
                                <div class="availability-info">
                                    <div class="info-row">
                                        <span class="info-label">In Stock:</span>
                                        <span id="productQuantity" class="info-value"></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Unit:</span>
                                        <span id="productUnit" class="info-value"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h5 class="fw-bold">Seller</h5>
                                <p id="productSeller" class="mb-0"></p>
                            </div>
                            
                            <div class="mb-5">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <h5 class="fw-bold mb-0">Quantity</h5>
                                    </div>
                                    <div class="col-auto">
                                        <div class="quantity-control">
                                            <button type="button" class="quantity-btn" id="decreaseQty">−</button>
                                            <input type="number" class="quantity-input" id="quantityInput" value="1" min="1" max="999">
                                            <button type="button" class="quantity-btn" id="increaseQty">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-3">
                                <button type="button" class="btn btn-success btn-lg add-to-cart-btn" id="addToCartBtn">
                                    <i data-feather="shopping-cart" class="me-2"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="errorState" class="text-center d-none">
                <div class="alert alert-danger">
                    <h4>Product Not Found</h4>
                    <p>The product you're looking for doesn't exist or has been removed.</p>
                    <a href="/buyer/products.php" class="btn btn-outline-success">Back to Products</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-firestore-compat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/dialogs.js"></script>
    <script src="../assets/js/cart.js"></script>

    <script>
        let currentProduct = null;
        let maxQuantity = 999;
        let isAddingToCart = false;

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function updateQuantityDisplay(quantity) {
            document.getElementById('quantityInput').value = quantity;
            document.getElementById('addToCartBtn').disabled = quantity < 1 || quantity > maxQuantity;
        }

        function initQuantityControls() {
            const decreaseBtn = document.getElementById('decreaseQty');
            const increaseBtn = document.getElementById('increaseQty');
            const quantityInput = document.getElementById('quantityInput');

            decreaseBtn.onclick = function() {
                const currentQty = parseInt(quantityInput.value);
                if (currentQty > 1) {
                    updateQuantityDisplay(currentQty - 1);
                }
            };

            increaseBtn.onclick = function() {
                const currentQty = parseInt(quantityInput.value);
                if (currentQty < maxQuantity) {
                    updateQuantityDisplay(currentQty + 1);
                }
            };

            quantityInput.oninput = function() {
                let qty = parseInt(quantityInput.value) || 1;
                if (qty < 1) qty = 1;
                if (qty > maxQuantity) qty = maxQuantity;
                updateQuantityDisplay(qty);
            };
        }

        async function loadProduct() {
            showLoading();

            waitForFirebase(async () => {
                try {
                    const doc = await firebase.firestore().collection('products').doc('<?php echo $productId; ?>').get();
                    hideLoading();

                    if (!doc.exists) {
                        document.getElementById('productContent').classList.add('d-none');
                        document.getElementById('errorState').classList.remove('d-none');
                        return;
                    }

                    const data = doc.data();
                    let sellerName = data.sellerName || 'Unknown Seller';

                    // If seller name is not available in product data, fetch from users collection
                    if (!data.sellerName && data.sellerId) {
                        try {
                            const userDoc = await firebase.firestore().collection('users').doc(data.sellerId).get();
                            if (userDoc.exists) {
                                const userData = userDoc.data();
                                sellerName = userData.name || 'Unknown Seller';
                            }
                        } catch (userError) {
                            console.warn('Could not fetch seller name from users collection:', userError);
                        }
                    }

                    currentProduct = {
                        id: doc.id,
                        name: data.name || 'Unknown Product',
                        price: data.price || 0,
                        description: data.description || 'No description available.',
                        category: data.category || 'Uncategorized',
                        organic: !!data.organic,
                        quantity: data.quantity || 0,
                        unit: data.unit || 'kg',
                        sellerId: data.sellerId || '',
                        sellerName: sellerName,
                        imageUrl: data.imageUrl || null,
                        imageData: data.imageData || null
                    };

                    maxQuantity = Math.min(currentProduct.quantity, 999);
                    updateQuantityDisplay(1);

                    displayProduct();
                    initQuantityControls();
                    feather.replace();
                } catch (error) {
                    hideLoading();
                    console.error('Error loading product:', error);
                    document.getElementById('productContent').classList.add('d-none');
                    document.getElementById('errorState').classList.remove('d-none');
                }
            });
        }

        function displayProduct() {
            const product = currentProduct;

            document.getElementById('productName').textContent = product.name;
            document.getElementById('productPrice').textContent = `₱${product.price.toFixed(2)}`;
            document.getElementById('productDescription').textContent = product.description;
            const unit = product.unit || 'kg';
            document.getElementById('productQuantity').textContent = product.quantity > 0 ? product.quantity : 'Out of Stock';
            document.getElementById('productUnit').textContent = product.quantity > 0 ? unit : '';
            document.getElementById('productSeller').textContent = product.sellerName;

            const categoryDiv = document.getElementById('productCategory');
            const displayCategory = product.category === 'Other' && product.specificName ? product.specificName : product.category;
            categoryDiv.innerHTML = `
                <span class="badge bg-success fs-6">${displayCategory}</span>
                ${product.organic ? '<span class="badge bg-info fs-6 ms-2">Organic</span>' : ''}
            `;

            const productImage = document.getElementById('productImage');
            const noImage = document.getElementById('noImage');
            const imageSrc = product.imageUrl || product.imageData;

            if (imageSrc) {
                productImage.src = imageSrc;
                productImage.alt = product.name;
                productImage.classList.remove('d-none');
                noImage.classList.add('d-none');
            } else {
                productImage.classList.add('d-none');
                noImage.classList.remove('d-none');
                feather.replace();
            }

            document.getElementById('addToCartBtn').disabled = product.quantity < 1;
        }

        function handleAddToCart() {
            if (isAddingToCart) return;
            
            if (!currentProduct) {
                showToast({
                    title: 'Error',
                    message: 'Product data not available.',
                    type: 'error'
                });
                return;
            }

            const quantity = parseInt(document.getElementById('quantityInput').value);

            if (quantity < 1) {
                showToast({
                    title: 'Invalid Quantity',
                    message: 'Please enter a valid quantity.',
                    type: 'warning'
                });
                return;
            }

            if (quantity > currentProduct.quantity) {
                showToast({
                    title: 'Insufficient Stock',
                    message: `Only ${currentProduct.quantity} items available.`,
                    type: 'warning'
                });
                return;
            }

            const addBtn = document.getElementById('addToCartBtn');
            const originalText = addBtn.innerHTML;
            addBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Adding...';
            addBtn.disabled = true;
            isAddingToCart = true;

            if (typeof window.addToCart === 'function') {
                window.addToCart(currentProduct, quantity)
                    .then(() => {
                        showToast({
                            title: 'Success!',
                            message: `${currentProduct.name} has been added to your cart!`,
                            type: 'success'
                        });
                        
                        // Also show success dialog
                        if (typeof showAlert === 'function') {
                            showAlert({
                                title: 'Added to Cart',
                                message: `${currentProduct.name} has been successfully added to your cart!`,
                                type: 'success'
                            });
                        }
                        
                        updateCartBadge();
                        
                        setTimeout(() => {
                            addBtn.innerHTML = originalText;
                            addBtn.disabled = currentProduct.quantity < 1;
                            isAddingToCart = false;
                        }, 1500);
                    })
                    .catch(error => {
                        console.error('Error adding to cart:', error);
                        showToast({
                            title: 'Error',
                            message: 'Failed to add item to cart. Please try again.',
                            type: 'error'
                        });
                        
                        addBtn.innerHTML = originalText;
                        addBtn.disabled = false;
                        isAddingToCart = false;
                    });
            } else {
                showToast({
                    title: 'Error',
                    message: 'Cart functionality not available. Please refresh the page.',
                    type: 'error'
                });
                
                addBtn.innerHTML = originalText;
                addBtn.disabled = false;
                isAddingToCart = false;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadProduct();

            const addToCartBtn = document.getElementById('addToCartBtn');
            addToCartBtn.onclick = handleAddToCart;
            
            waitForFirebase(() => {
                if (typeof updateCartBadge === 'function') {
                    updateCartBadge();
                }
            });
        });
    </script>
</body>

</html>