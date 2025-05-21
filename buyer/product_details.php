<?php
session_start();
include '../includes/auth.php';
include '../includes/functions.php';

// Ensure user is logged in as a buyer
ensureUserLoggedIn('buyer');

// Get product ID from URL
$productId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

// Redirect if no product ID is provided
if (!$productId) {
    header('Location: products.php');
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="mb-4">
            <a href="products.php" class="btn btn-outline-success">
                <i data-feather="arrow-left" class="me-1"></i> Back to Products
            </a>
        </div>
        
        <div id="product-details-container">
            <!-- Product details will be loaded here -->
            <div class="text-center py-5" id="loading">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading product details...</p>
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
            
            // Load product details
            loadProductDetails('<?php echo $productId; ?>');
            
            // Update cart badge
            updateCartBadge();
            
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
        });
        
        // Current product for add to cart modal
        let currentProduct = null;
        
        // Function to load product details
        function loadProductDetails(productId) {
            const productDetailsContainer = document.getElementById('product-details-container');
            const loading = document.getElementById('loading');
            
            // Get product from Firestore
            firebase.firestore().collection('products').doc(productId).get()
                .then(doc => {
                    loading.style.display = 'none';
                    
                    if (!doc.exists) {
                        productDetailsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                Product not found or has been removed.
                            </div>
                        `;
                        return;
                    }
                    
                    const product = {
                        id: doc.id,
                        ...doc.data()
                    };
                    
                    // Store current product for add to cart functionality
                    currentProduct = {
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        sellerId: product.sellerId,
                        sellerName: product.sellerName
                    };
                    
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
                    
                    // Create product details
                    productDetailsContainer.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-5 mb-4 mb-md-0">
                                        <div class="bg-light rounded text-center p-4" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                            <img src="${imagePlaceholder}" alt="${product.name}" style="max-height: 100%; max-width: 100%;">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="mb-2">
                                            <span class="badge bg-success">${product.category}</span>
                                            ${product.organic ? '<span class="badge bg-info ms-2">Organic</span>' : ''}
                                        </div>
                                        <h2 class="mb-3">${product.name}</h2>
                                        <h3 class="text-success mb-4">₱${product.price.toFixed(2)}</h3>
                                        
                                        <p class="mb-4">${product.description}</p>
                                        
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="input-group me-3" style="width: 120px;">
                                                <button class="btn btn-outline-secondary" type="button" id="decrease-quantity">-</button>
                                                <input type="number" class="form-control text-center" id="product-quantity" value="1" min="1">
                                                <button class="btn btn-outline-secondary" type="button" id="increase-quantity">+</button>
                                            </div>
                                            <button class="btn btn-success" id="add-to-cart-btn">
                                                <i data-feather="shopping-cart" class="me-1"></i> Add to Cart
                                            </button>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p class="mb-1"><strong>Availability:</strong> ${product.quantity > 0 ? 'In Stock' : 'Out of Stock'}</p>
                                            <p class="mb-1"><strong>Seller:</strong> ${product.sellerName}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Initialize Feather icons for dynamically added content
                    feather.replace();
                    
                    // Add event listeners for quantity buttons
                    document.getElementById('decrease-quantity').addEventListener('click', function() {
                        const quantityInput = document.getElementById('product-quantity');
                        const currentValue = parseInt(quantityInput.value);
                        if (currentValue > 1) {
                            quantityInput.value = currentValue - 1;
                        }
                    });
                    
                    document.getElementById('increase-quantity').addEventListener('click', function() {
                        const quantityInput = document.getElementById('product-quantity');
                        const currentValue = parseInt(quantityInput.value);
                        quantityInput.value = currentValue + 1;
                    });
                    
                    // Add event listener for add to cart button
                    document.getElementById('add-to-cart-btn').addEventListener('click', function() {
                        const quantity = parseInt(document.getElementById('product-quantity').value);
                        
                        if (quantity < 1) {
                            alert('Please enter a valid quantity');
                            return;
                        }
                        
                        addToCart(currentProduct, quantity);
                    });
                })
                .catch(error => {
                    loading.style.display = 'none';
                    console.error("Error getting product details: ", error);
                    
                    productDetailsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            Error loading product details. Please try again later.
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>
