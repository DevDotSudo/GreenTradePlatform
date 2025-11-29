<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';
include '../includes/functions.php';
ensureUserLoggedIn('buyer');

$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';
$search   = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Products - Green Trade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Browse Agricultural Products</h1>
                <p class="page-subtitle">Discover fresh, high-quality products from local farmers</p>
            </div>

            <!-- Search & Cart Bar -->
            <div class="search-cart-bar">
                <div class="search-section">
                    <form action="/buyer/products.php" method="get" class="search-form">
                        <div class="search-input-group">
                            <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?php echo $search; ?>">
                            <button type="submit" class="search-button">
                                <span class="search-icon">🔍</span>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="cart-section">
                    <a href="/buyer/cart.php" class="cart-button">
                        <span class="cart-icon">🛒</span>
                        View Cart
                        <span class="cart-badge" id="cart-badge">0</span>
                    </a>
                </div>
            </div>

            <!-- Categories -->
            <div class="categories-section">
                <ul class="categories-list">
                    <?php
                    $categories = ['', 'Vegetables', 'Fruits', 'Rice', 'Fish', 'Meat', 'Other'];
                    foreach ($categories as $cat) {
                        $active = $category === $cat ? 'active' : '';
                        $label = $cat === '' ? 'All Products' : $cat;
                        $url = '/buyer/products.php';
                        if ($cat !== '') {
                            $url .= "?category=" . urlencode($cat);
                            if ($search) {
                                $url .= "&search=" . urlencode($search);
                            }
                        } else if ($search) {
                            $url .= "?search=" . urlencode($search);
                        }
                        echo "<li class='category-item'><a class='category-link $active' href='$url'>$label</a></li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Products Grid -->
            <div class="products-section">
                <div class="products-grid" id="products-container">
                    <div class="empty-state d-none" id="no-products">
                        <div class="empty-icon">📦</div>
                        <h3 class="empty-title">No products found</h3>
                        <p class="empty-description">Try a different search or category.</p>
                    </div>
                    <!-- Loading state will be added here -->
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/app.js"></script>
    <script src="../assets/js/services/cartService.js"></script>
    <script src="../assets/js/cart.js"></script>
    <script src="../assets/js/dialogs.js"></script>
    <script src="../assets/js/main.js"></script>

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

        .search-cart-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-6);
            margin-bottom: var(--space-8);
            flex-wrap: wrap;
        }

        .search-section {
            flex: 1;
            min-width: 300px;
        }

        .search-form {
            width: 100%;
        }

        .search-input-group {
            display: flex;
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            transition: all var(--transition-fast);
        }

        .search-input-group:focus-within {
            box-shadow: var(--shadow-md);
            border-color: var(--primary-500);
        }

        .search-input {
            flex: 1;
            border: none;
            padding: var(--space-4) var(--space-6);
            font-size: 1rem;
            outline: none;
            background: transparent;
        }

        .search-button {
            background: var(--primary-500);
            color: white;
            border: none;
            padding: var(--space-4) var(--space-6);
            cursor: pointer;
            transition: background var(--transition-fast);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .search-button:hover {
            background: var(--primary-600);
        }

        .search-icon {
            font-size: 1.125rem;
        }

        .cart-section {
            flex-shrink: 0;
        }

        .cart-button {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-4) var(--space-6);
            background: white;
            color: var(--primary-600);
            text-decoration: none;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all var(--transition-fast);
            font-weight: 600;
        }

        .cart-button:hover {
            color: var(--primary-700);
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .cart-icon {
            font-size: 1.25rem;
        }

        .cart-badge {
            background: var(--primary-500);
            color: white;
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }

        .categories-section {
            margin-bottom: var(--space-8);
        }

        .categories-list {
            display: flex;
            gap: var(--space-2);
            list-style: none;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
        }

        .category-link {
            display: block;
            padding: var(--space-3) var(--space-6);
            background: white;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
            transition: all var(--transition-fast);
            font-weight: 500;
        }

        .category-link:hover {
            background: var(--primary-50);
            color: var(--primary-700);
            border-color: var(--primary-300);
        }

        .category-link.active {
            background: var(--primary-500);
            color: white;
            border-color: var(--primary-500);
        }

        .products-section {
            margin-bottom: var(--space-8);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--space-6);
        }

        .product-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--neutral-200);
            overflow: hidden;
            transition: all var(--transition-normal);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: var(--primary-200);
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--accent-500));
            opacity: 0;
            transition: opacity var(--transition-fast);
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--accent-500));
            opacity: 0;
            transition: opacity var(--transition-fast);
        }

        .product-card:hover::before {
            opacity: 1;
        }

        .product-image {
            height: 220px;
            background: linear-gradient(135deg, var(--neutral-50), var(--primary-50));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            max-height: 100%;
            max-width: 100%;
            object-fit: cover;
            transition: transform var(--transition-normal);
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .availability-badges {
            position: absolute;
            top: 12px;
            right: 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            z-index: 10;
        }

        .availability-badge {
            padding: 6px 12px;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            white-space: nowrap;
        }

        .badge-success {
            background: rgba(34, 197, 94, 0.95);
            color: white;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.95);
            color: white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .badge-info {
            background: rgba(14, 165, 233, 0.95);
            color: white;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
        }

        .badge-info {
            background: rgba(14, 165, 233, 0.95);
            color: white;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
        }

        .product-content {
            padding: var(--space-6);
            background: white;
        }

        .product-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--neutral-900);
            margin-bottom: var(--space-2);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-600);
            margin-bottom: var(--space-3);
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }

        .product-price::before {
            content: '₱';
            font-size: 0.875rem;
            color: var(--neutral-500);
        }

        .product-description {
            color: var(--neutral-600);
            margin-bottom: var(--space-4);
            line-height: 1.5;
            font-size: 0.875rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-seller {
            color: var(--neutral-500);
            font-size: 0.75rem;
            margin-bottom: var(--space-4);
            font-weight: 500;
        }

        .product-actions {
            display: flex;
            gap: var(--space-3);
            margin-top: auto;
        }

        .product-actions .btn {
            flex: 1;
            justify-content: center;
            padding: var(--space-3) var(--space-4);
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast);
            border: 1px solid transparent;
        }

        .product-actions .btn-outline-primary {
            background: transparent;
            color: var(--primary-600);
            border-color: var(--primary-200);
        }

        .product-actions .btn-outline-primary:hover {
            background: var(--primary-50);
            border-color: var(--primary-300);
            color: var(--primary-700);
            transform: translateY(-1px);
        }

        .product-actions .btn-primary {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
        }

        .product-actions .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
        }

        .product-actions .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .loading-state {
            grid-column: 1 / -1;
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

        .empty-state {
            grid-column: 1 / -1;
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

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: var(--space-6) 0;
            }

            .page-title {
                font-size: 2rem;
            }

            .search-cart-bar {
                flex-direction: column;
                gap: var(--space-4);
            }

            .search-section {
                width: 100%;
                min-width: auto;
            }

            .cart-section {
                width: 100%;
            }

            .cart-button {
                width: 100%;
                justify-content: center;
            }

            .categories-list {
                justify-content: center;
            }

            .products-grid {
                grid-template-columns: 1fr;
                gap: var(--space-4);
            }

            .product-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.75rem;
            }

            .product-content {
                padding: var(--space-4);
            }

            .product-title {
                font-size: 1.125rem;
            }

            .product-price {
                font-size: 1.25rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            waitForFirebase(() => {
                // Set global user ID for cart functions
                window.currentUserId = '<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>';
                loadProducts();
                updateCartBadge();
            });
        });

        let currentProduct = null;
        let allProducts = [];

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        async function loadProducts() {
            const category = '<?php echo $category; ?>';
            const search = '<?php echo $search; ?>';
            const productsContainer = document.getElementById('products-container');
            const noProducts = document.getElementById('no-products');

            if (window.loadingOverlay) {
                window.loadingOverlay.show('Loading products...');
            }

            try {
                let query = firebase.firestore().collection('products');

                if (category && category !== '') {
                    query = query.where('category', '==', category);
                }

                const snapshot = await query.get();
                if (window.loadingOverlay) window.loadingOverlay.hide();
                if (snapshot.empty) {
                    noProducts.classList.remove('d-none');
                    return;
                }

                productsContainer.innerHTML = '';
                allProducts = [];
                snapshot.forEach(doc => allProducts.push(Object.assign({ id: doc.id }, doc.data())));

                // Fetch seller names for all products that don't have them
                const sellerPromises = allProducts.map(async (product) => {
                    if (!product.sellerName && product.sellerId) {
                        try {
                            const userDoc = await firebase.firestore().collection('users').doc(product.sellerId).get();
                            if (userDoc.exists) {
                                const userData = userDoc.data();
                                product.sellerName = userData.name || 'Unknown Seller';
                            }
                        } catch (userError) {
                            console.warn('Could not fetch seller name for product:', product.id, userError);
                        }
                    }
                    return product;
                });

                allProducts = await Promise.all(sellerPromises);

                let filteredProducts = allProducts;
                if (search) {
                    const q = search.toLowerCase();
                    filteredProducts = allProducts.filter(p => {
                        const name = (p.name || '').toString().toLowerCase();
                        const desc = (p.description || '').toString().toLowerCase();
                        return name.includes(q) || desc.includes(q);
                    });
                }

                if (filteredProducts.length === 0) {
                    noProducts.classList.remove('d-none');
                    return;
                }

                const productsWithSellerNames = filteredProducts;

                productsWithSellerNames.forEach(product => {
                    try {
                        const productCard = document.createElement('div');
                        productCard.className = 'product-card';

                        const hasImage = !!(product.imageData || product.imageUrl);
                        let imagePlaceholder = '📦';
                        const imageMap = {
                            Vegetables: '🥬',
                            Fruits: '🍎',
                            Rice: '🌾',
                            Fish: '🐟',
                            Meat: '🥩'
                        };
                        if (!hasImage && imageMap[product.category]) {
                            imagePlaceholder = imageMap[product.category];
                        }

                        const priceNum = Number(product.price) || 0;
                        const descText = (product.description || '').toString();
                        const shortDesc = descText.length > 80 ? descText.substring(0, 80) + '...' : descText;
                        const sellerName = product.sellerName || 'Unknown Seller';
                        const prodName = product.name || 'Untitled Product';
                        const imageSrc = product.imageUrl || product.imageData || null;
                        const quantity = Number(product.quantity) || 0;
                        const unit = product.unit || 'kg';
                        const isOutOfStock = quantity <= 0;
                        const statusText = isOutOfStock ? 'Out of Stock' : `In Stock (${quantity})`;
                        const unitText = isOutOfStock ? '' : `Unit : ${unit}`;
                        const statusClass = isOutOfStock ? 'badge-danger' : 'badge-success';
                        const unitClass = 'badge-info';

                        productCard.innerHTML = `
                            <div class="product-image">
                                ${hasImage ? `<img src="${imageSrc}" alt="${escapeHtml(prodName)}">` : `<span style="font-size: 4rem;">${imagePlaceholder}</span>`}
                                <div class="availability-badges">
                                    <div class="availability-badge ${statusClass}">${statusText}</div>
                                    ${unitText ? `<div class="availability-badge ${unitClass}">${unitText}</div>` : ''}
                                </div>
                            </div>
                            <div class="product-content">
                                <h3 class="product-title">${escapeHtml(prodName)}</h3>
                                <div class="product-price">₱${priceNum.toFixed(2)}</div>
                                <p class="product-description">${escapeHtml(shortDesc)}</p>
                                <p class="product-seller">Seller: ${escapeHtml(sellerName)}</p>
                                <div class="product-actions">
                                    <button class="btn btn-outline-primary view-details" data-id="${product.id}">View Details</button>
                                    <button class="btn btn-primary add-to-cart ${isOutOfStock ? 'disabled' : ''}"
                                        data-id="${product.id}" data-name="${escapeHtml(prodName)}"
                                        data-price="${priceNum}" data-seller="${product.sellerId || ''}"
                                        data-seller-name="${escapeHtml(sellerName)}" ${isOutOfStock ? 'disabled' : ''}>
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        `;
                        productsContainer.appendChild(productCard);
                    } catch (e) {
                        console.error('Error rendering product', product, e);
                    }
                });

                // Add event listeners
                document.querySelectorAll('.view-details').forEach(btn => {
                    btn.addEventListener('click', () => {
                        window.location.href = `/buyer/product_details.php?id=${btn.dataset.id}`;
                    });
                });

                document.querySelectorAll('.add-to-cart').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const productData = {
                            id: btn.dataset.id,
                            name: btn.dataset.name,
                            price: parseFloat(btn.dataset.price) || 0,
                            sellerId: btn.dataset.seller || '',
                            sellerName: btn.dataset.sellerName || 'Unknown Seller'
                        };

                        const productFromList = allProducts.find(p => p.id === productData.id);
                        if (productFromList) {
                            productData.imageUrl = productFromList.imageUrl || null;
                            productData.imageData = productFromList.imageData || null;
                        }

                        currentProduct = productData;
                        showAddToCartDialog();
                    });
                });

            } catch (err) {
                if (window.loadingOverlay) window.loadingOverlay.hide();
                console.error("Error getting products: ", err);
                showToast({
                    title: 'Error',
                    message: 'Error loading products. Please try again later.',
                    type: 'error'
                });
            }
        }

        function showAddToCartDialog() {
            if (typeof dialogs !== 'undefined' && typeof dialogs.prompt === 'function') {
                dialogs.prompt({
                    title: 'Add to Cart',
                    message: `How many "${currentProduct.name}" would you like to add to cart?`,
                    type: 'info',
                    placeholder: 'Quantity',
                    defaultValue: '1'
                }).then(value => {
                    if (value === null) return;
                    const qty = parseInt(value);
                    if (isNaN(qty) || qty < 1) {
                        showToast({
                            title: 'Invalid Quantity',
                            message: 'Please enter a valid quantity (minimum 1).',
                            type: 'warning'
                        });
                        return;
                    }
                    if (typeof window.addToCart === 'function') {
                        window.addToCart(currentProduct, qty)
                            .then(() => {
                                updateCartBadge();
                            })
                            .catch(err => {
                                console.error('Error adding to cart:', err);
                            });
                    } else {
                        console.error('addToCart function not available');
                        showToast({
                            title: 'Error',
                            message: 'Add to cart function is not available. Please refresh the page.',
                            type: 'error'
                        });
                    }
                }).catch(err => {
                    console.error('Prompt error', err);
                    showToast({ title: 'Error', message: 'Could not add to cart.', type: 'error' });
                });
            } else {
                // Fallback to browser prompt
                const qty = parseInt(prompt(`How many "${currentProduct.name}" would you like to add to cart?`, '1'));
                if (isNaN(qty) || qty < 1) {
                    showToast({
                        title: 'Invalid Quantity',
                        message: 'Please enter a valid quantity (minimum 1).',
                        type: 'warning'
                    });
                    return;
                }
                if (typeof window.addToCart === 'function') {
                    window.addToCart(currentProduct, qty)
                        .then(() => {
                            updateCartBadge();
                        })
                        .catch(err => {
                            console.error('Error adding to cart:', err);
                        });
                } else {
                    console.error('addToCart function not available');
                    showToast({
                        title: 'Error',
                        message: 'Add to cart function is not available. Please refresh the page.',
                        type: 'error'
                    });
                }
            }
        }

        function updateCartBadge() {
            const badge = document.getElementById('cart-badge');
            if (!badge) return;

            const userId = '<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>';

            if (!userId) {
                badge.textContent = '0';
                return;
            }

            firebase.firestore().collection('carts')
                .doc(userId)
                .get()
                .then(doc => {
                    let totalItems = 0;
                    if (doc.exists) {
                        const cart = doc.data();
                        const items = Array.isArray(cart.items) ? cart.items : [];
                        totalItems = items.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
                    }
                    badge.textContent = totalItems;
                })
                .catch(error => {
                    console.error("Error updating cart badge: ", error);
                    badge.textContent = '0';
                });
        }
    </script>
</body>

</html>