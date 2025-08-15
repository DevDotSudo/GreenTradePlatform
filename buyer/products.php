<?php
session_start();
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Browse Agricultural Products</h1>
                <p class="page-subtitle">Discover fresh, high-quality products from local farmers</p>
            </div>
            
            <!-- Search & Cart Bar -->
            <div class="search-cart-bar">
                <div class="search-section">
                    <form action="products.php" method="get" class="search-form">
                        <div class="search-input-group">
                            <input type="text" name="search" class="search-input" placeholder="Search products..." value="<?php echo $search; ?>">
                            <button type="submit" class="search-button">
                                <span class="search-icon">🔍</span>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="cart-section">
                    <a href="cart.php" class="cart-button">
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
                        echo "<li class='category-item'><a class='category-link $active' href='products.php" . ($cat ? "?category=$cat" : "") . "'>$label</a></li>";
                    }
                    ?>
                </ul>
            </div>
            
            <!-- Products Grid -->
            <div class="products-section">
                <div class="products-grid" id="products-container">
                    <div class="loading-state" id="loading">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Loading products...</p>
                    </div>
                    <div class="empty-state d-none" id="no-products">
                        <div class="empty-icon">📦</div>
                        <h3 class="empty-title">No products found</h3>
                        <p class="empty-description">Try a different search or category.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/cart.js"></script>
    <script src="../assets/js/dialogs.js"></script>
    
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
        box-shadow: var(--shadow-md);
        border: 1px solid var(--gray-200);
        overflow: hidden;
        transition: all var(--transition-normal);
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .product-image {
        height: 200px;
        background: linear-gradient(135deg, var(--gray-50), var(--primary-50));
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .product-image img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }

    .product-content {
        padding: var(--space-6);
    }

    .product-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: var(--space-2);
    }

    .product-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-600);
        margin-bottom: var(--space-3);
    }

    .product-description {
        color: var(--gray-600);
        margin-bottom: var(--space-4);
        line-height: 1.6;
    }

    .product-seller {
        color: var(--gray-500);
        font-size: 0.875rem;
        margin-bottom: var(--space-4);
    }

    .product-actions {
        display: flex;
        gap: var(--space-3);
    }

    .product-actions .btn {
        flex: 1;
        justify-content: center;
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
                loadProducts();
                updateCartBadge();
            });
        });

        let currentProduct = null;

        function loadProducts() {
            const category = '<?php echo $category; ?>';
            const search   = '<?php echo $search; ?>';
            const productsContainer = document.getElementById('products-container');
            const loading   = document.getElementById('loading');
            const noProducts = document.getElementById('no-products');
            
            let query = firebase.firestore().collection('products');
            if (category) query = query.where('category', '==', category);

            query.get().then(snapshot => {
                loading.style.display = 'none';
                if (snapshot.empty) {
                    noProducts.classList.remove('d-none');
                    return;
                }
                productsContainer.innerHTML = '';
                const products = [];
                snapshot.forEach(doc => products.push({ id: doc.id, ...doc.data() }));

                let filteredProducts = products;
                if (search) {
                    filteredProducts = products.filter(p =>
                        p.name.toLowerCase().includes(search.toLowerCase()) ||
                        p.description.toLowerCase().includes(search.toLowerCase())
                    );
                }
                if (filteredProducts.length === 0) {
                    noProducts.classList.remove('d-none');
                    return;
                }

                filteredProducts.forEach(product => {
                    const productCard = document.createElement('div');
                    productCard.className = 'product-card';

                    const hasImage = !!product.imageData;
                    let imagePlaceholder = '📦';
                    if (!hasImage) {
                        const map = {
                            Vegetables: '🥬',
                            Fruits: '🍎',
                            Rice: '🌾',
                            Fish: '🐟',
                            Meat: '🥩'
                        };
                        if (map[product.category]) {
                            imagePlaceholder = map[product.category];
                        }
                    }

                    productCard.innerHTML = `
                        <div class="product-image">
                            ${hasImage ? `<img src="${product.imageData}" alt="${product.name}">` : `<span style="font-size: 4rem;">${imagePlaceholder}</span>`}
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">${product.name}</h3>
                            <div class="product-price">₱${product.price.toFixed(2)}</div>
                            <p class="product-description">${product.description.substring(0, 80)}${product.description.length > 80 ? '...' : ''}</p>
                            <p class="product-seller">Seller: ${product.sellerName}</p>
                            <div class="product-actions">
                                <button class="btn btn-outline-primary view-details" data-id="${product.id}">View Details</button>
                                <button class="btn btn-primary add-to-cart"
                                    data-id="${product.id}" data-name="${product.name}"
                                    data-price="${product.price}" data-seller="${product.sellerId}"
                                    data-seller-name="${product.sellerName}">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    `;
                    productsContainer.appendChild(productCard);
                });

                // Add event listeners
                document.querySelectorAll('.view-details').forEach(btn => {
                    btn.addEventListener('click', () => {
                        window.location.href = `product_details.php?id=${btn.dataset.id}`;
                    });
                });

                document.querySelectorAll('.add-to-cart').forEach(btn => {
                    btn.addEventListener('click', () => {
                        currentProduct = {
                            id: btn.dataset.id,
                            name: btn.dataset.name,
                            price: parseFloat(btn.dataset.price),
                            sellerId: btn.dataset.seller,
                            sellerName: btn.dataset.sellerName
                        };
                        showAddToCartDialog();
                    });
                });

            }).catch(err => {
                loading.style.display = 'none';
                console.error("Error getting products: ", err);
                showToast({
                    title: 'Error',
                    message: 'Error loading products. Please try again later.',
                    type: 'error'
                });
            });
        }

        function showAddToCartDialog() {
            const quantity = prompt(`How many "${currentProduct.name}" would you like to add to cart?`, '1');
            const qty = parseInt(quantity);
            
            if (isNaN(qty) || qty < 1) {
                showToast({
                    title: 'Invalid Quantity',
                    message: 'Please enter a valid quantity (minimum 1).',
                    type: 'warning'
                });
                return;
            }
            
            addToCart(currentProduct, qty);
        }

        function addToCart(product, quantity) {
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            
            firebase.firestore().collection('carts')
                .where('userId', '==', userId)
                .where('productId', '==', product.id)
                .get()
                .then(snapshot => {
                    if (snapshot.empty) {
                        // Add new item to cart
                        return firebase.firestore().collection('carts').add({
                            userId: userId,
                            productId: product.id,
                            productName: product.name,
                            price: product.price,
                            quantity: quantity,
                            sellerId: product.sellerId,
                            sellerName: product.sellerName,
                            addedAt: firebase.firestore.FieldValue.serverTimestamp()
                        });
                    } else {
                        // Update existing item quantity
                        const doc = snapshot.docs[0];
                        return doc.ref.update({
                            quantity: doc.data().quantity + quantity
                        });
                    }
                })
                .then(() => {
                    showToast({
                        title: 'Success',
                        message: `${quantity} ${quantity === 1 ? 'item' : 'items'} added to cart!`,
                        type: 'success'
                    });
                    updateCartBadge();
                })
                .catch(error => {
                    console.error("Error adding to cart: ", error);
                    showToast({
                        title: 'Error',
                        message: 'Failed to add item to cart. Please try again.',
                        type: 'error'
                    });
                });
        }

        function updateCartBadge() {
            const userId = '<?php echo $_SESSION['user_id']; ?>';
            
            firebase.firestore().collection('carts')
                .where('userId', '==', userId)
                .get()
                .then(snapshot => {
                    let totalItems = 0;
                    snapshot.forEach(doc => {
                        const cart = doc.data();
                        totalItems += cart.quantity || 0;
                    });
                    document.getElementById('cart-badge').textContent = totalItems;
                })
                .catch(error => {
                    console.error("Error updating cart badge: ", error);
                });
        }
    </script>
</body>
</html>
