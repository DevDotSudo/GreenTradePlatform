<?php
session_start();
include '../includes/auth.php';
include '../includes/functions.php';

// Ensure user is logged in as a buyer
ensureUserLoggedIn('buyer');

// Get category from URL if present
$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Products - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1 class="mb-4">Browse Agricultural Products</h1>
        
        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form action="products.php" method="get" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn btn-success">Search</button>
                </form>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end mt-3 mt-md-0">
                <a href="cart.php" class="btn btn-outline-success">
                    <i data-feather="shopping-cart" class="me-1"></i> 
                    View Cart <span id="cart-badge" class="badge bg-success ms-1">0</span>
                </a>
            </div>
        </div>
        
        <!-- Categories Filter -->
        <div class="mb-4">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link <?php echo $category === '' ? 'active' : ''; ?>" href="products.php">All Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $category === 'Vegetables' ? 'active' : ''; ?>" href="products.php?category=Vegetables">Vegetables</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $category === 'Fruits' ? 'active' : ''; ?>" href="products.php?category=Fruits">Fruits</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $category === 'Rice' ? 'active' : ''; ?>" href="products.php?category=Rice">Rice</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $category === 'Fish' ? 'active' : ''; ?>" href="products.php?category=Fish">Fish</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $category === 'Meat' ? 'active' : ''; ?>" href="products.php?category=Meat">Meat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $category === 'Other' ? 'active' : ''; ?>" href="products.php?category=Other">Other</a>
                </li>
            </ul>
        </div>
        
        <!-- Products Grid -->
        <div class="row" id="products-container">
            <!-- Products will be loaded here -->
            <div class="col-12 text-center py-5" id="loading">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading products...</p>
            </div>
            
            <div class="col-12 text-center py-5 d-none" id="no-products">
                <i data-feather="package" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                <h4>No products found</h4>
                <p class="text-muted">Try a different search or category.</p>
            </div>
        </div>
    </div>
    
    <!-- Add to Cart Modal -->
    <div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addToCartModalLabel">Add to Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <h4 id="modal-product-name"></h4>
                        <p class="text-success fw-bold" id="modal-product-price"></p>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" min="1" value="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirm-add-to-cart">Add to Cart</button>
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
            
            // Load products
            loadProducts();
            
            // Update cart badge
            updateCartBadge();
        });
        
        // Current product for add to cart modal
        let currentProduct = null;
        
        // Function to load products
        function loadProducts() {
            const category = '<?php echo $category; ?>';
            const search = '<?php echo $search; ?>';
            const productsContainer = document.getElementById('products-container');
            const loading = document.getElementById('loading');
            const noProducts = document.getElementById('no-products');
            
            // Create a query to Firestore
            let query = firebase.firestore().collection('products');
            
            // Apply category filter if selected
            if (category) {
                query = query.where('category', '==', category);
            }
            
            // Execute the query
            query.get()
                .then(snapshot => {
                    loading.style.display = 'none';
                    
                    if (snapshot.empty) {
                        noProducts.classList.remove('d-none');
                        return;
                    }
                    
                    // Clear container
                    productsContainer.innerHTML = '';
                    
                    const products = [];
                    snapshot.forEach(doc => {
                        const product = {
                            id: doc.id,
                            ...doc.data()
                        };
                        products.push(product);
                    });
                    
                    // Filter by search if provided
                    let filteredProducts = products;
                    if (search) {
                        filteredProducts = products.filter(product => 
                            product.name.toLowerCase().includes(search.toLowerCase()) ||
                            product.description.toLowerCase().includes(search.toLowerCase())
                        );
                    }
                    
                    if (filteredProducts.length === 0) {
                        noProducts.classList.remove('d-none');
                        return;
                    }
                    
                    // Render products
                    filteredProducts.forEach(product => {
                        const col = document.createElement('div');
                        col.className = 'col-md-4 col-lg-3 mb-4';
                        
                        const card = document.createElement('div');
                        card.className = 'card h-100';
                        
                        // Default image placeholders based on category
                        let imagePlaceholder = '';
                        switch(product.category) {
                            case 'Vegetables':
                                imagePlaceholder = 'https://cdn.jsdelivr.net/npm/feather-icons/dist/icons/leaf.svg';
                                break;
                            case 'Fruits':
                                imagePlaceholder = 'https://cdn.jsdelivr.net/npm/feather-icons/dist/icons/fruit.svg';
                                break;
                            case 'Rice':
                                imagePlaceholder = 'https://cdn.jsdelivr.net/npm/feather-icons/dist/icons/package.svg';
                                break;
                            case 'Fish':
                                imagePlaceholder = 'https://cdn.jsdelivr.net/npm/feather-icons/dist/icons/fish.svg';
                                break;
                            case 'Meat':
                                imagePlaceholder = 'https://cdn.jsdelivr.net/npm/feather-icons/dist/icons/meat.svg';
                                break;
                            default:
                                imagePlaceholder = 'https://cdn.jsdelivr.net/npm/feather-icons/dist/icons/box.svg';
                        }
                        
                        card.innerHTML = `
                            <div class="card-img-top bg-light text-center p-3" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                <img src="${imagePlaceholder}" alt="${product.name}" style="max-height: 100%; max-width: 100%;">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">${product.name}</h5>
                                <p class="card-text text-success fw-bold">₱${product.price.toFixed(2)}</p>
                                <p class="card-text">${product.description.substring(0, 80)}${product.description.length > 80 ? '...' : ''}</p>
                                <p class="card-text text-muted small">Seller: ${product.sellerName}</p>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-outline-success btn-sm view-details" data-id="${product.id}">View Details</button>
                                    <button class="btn btn-success btn-sm add-to-cart" data-id="${product.id}" 
                                        data-name="${product.name}" data-price="${product.price}" 
                                        data-seller="${product.sellerId}" data-seller-name="${product.sellerName}">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        `;
                        
                        col.appendChild(card);
                        productsContainer.appendChild(col);
                    });
                    
                    // Add event listeners for view details buttons
                    document.querySelectorAll('.view-details').forEach(button => {
                        button.addEventListener('click', function() {
                            const productId = this.getAttribute('data-id');
                            window.location.href = `product_details.php?id=${productId}`;
                        });
                    });
                    
                    // Add event listeners for add to cart buttons
                    document.querySelectorAll('.add-to-cart').forEach(button => {
                        button.addEventListener('click', function() {
                            currentProduct = {
                                id: this.getAttribute('data-id'),
                                name: this.getAttribute('data-name'),
                                price: parseFloat(this.getAttribute('data-price')),
                                sellerId: this.getAttribute('data-seller'),
                                sellerName: this.getAttribute('data-seller-name')
                            };
                            
                            // Update modal content
                            document.getElementById('modal-product-name').textContent = currentProduct.name;
                            document.getElementById('modal-product-price').textContent = `₱${currentProduct.price.toFixed(2)}`;
                            
                            // Reset quantity to 1
                            document.getElementById('quantity').value = 1;
                            
                            // Show the modal
                            const modal = new bootstrap.Modal(document.getElementById('addToCartModal'));
                            modal.show();
                        });
                    });
                    
                    // Set up confirm add to cart button
                    document.getElementById('confirm-add-to-cart').addEventListener('click', function() {
                        const quantity = parseInt(document.getElementById('quantity').value);
                        
                        if (quantity < 1) {
                            alert('Please enter a valid quantity');
                            return;
                        }
                        
                        if (currentProduct) {
                            addToCart(currentProduct, quantity);
                        }
                        
                        // Hide the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addToCartModal'));
                        modal.hide();
                    });
                })
                .catch(error => {
                    loading.style.display = 'none';
                    console.error("Error getting products: ", error);
                    alert("Error loading products. Please try again later.");
                });
        }
    </script>
</body>
</html>
