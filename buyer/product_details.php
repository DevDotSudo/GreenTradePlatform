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

    <main class="container py-5">
        <a href="products.php" class="btn btn-outline-success mb-4 rounded-pill px-4 shadow-sm">
            <i data-feather="arrow-left" class="me-1"></i> Back to Products
        </a>

        <section id="product-details-container" class="card border-0 shadow-lg rounded-4">
            <div class="card-body text-center py-5" id="loading">
                <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 fw-semibold text-secondary">Loading product details...</p>
            </div>
        </section>
    </main>

    <!-- Add to Cart Modal -->
    <div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="addToCartModalLabel">Add to Cart</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <h4 id="modal-product-name" class="fw-bold"></h4>
                        <p class="text-success fw-bold fs-5" id="modal-product-price"></p>
                    </div>
                    <label for="quantity" class="form-label fw-semibold">Quantity</label>
                    <input type="number" class="form-control form-control-lg rounded-pill text-center shadow-sm" id="quantity" min="1" value="1">
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4 rounded-pill shadow-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success px-4 rounded-pill shadow-sm" id="confirm-add-to-cart">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/cart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            feather.replace();
            waitForFirebase(() => {
                loadProductDetails('<?php echo $productId; ?>');
                updateCartBadge();

                document.getElementById('confirm-add-to-cart').addEventListener('click', function () {
                    const quantity = parseInt(document.getElementById('quantity').value);
                    if (quantity < 1) {
                        alert('Please enter a valid quantity');
                        return;
                    }
                    if (currentProduct) addToCart(currentProduct, quantity);
                    bootstrap.Modal.getInstance(document.getElementById('addToCartModal')).hide();
                });
            });
        });

        let currentProduct = null;

        function loadProductDetails(productId) {
            const productDetailsContainer = document.getElementById('product-details-container');
            const loading = document.getElementById('loading');

            firebase.firestore().collection('products').doc(productId).get()
                .then(doc => {
                    loading.style.display = 'none';
                    if (!doc.exists) {
                        productDetailsContainer.innerHTML = `<div class="alert alert-danger rounded-4">Product not found or removed.</div>`;
                        return;
                    }

                    const product = { id: doc.id, ...doc.data() };
                    currentProduct = {
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        sellerId: product.sellerId,
                        sellerName: product.sellerName
                    };

                    let imagePlaceholder = {
                        Vegetables: 'leaf',
                        Fruits: 'aperture',
                        Rice: 'package',
                        Fish: 'fish',
                        Meat: 'drumstick'
                    }[product.category] || 'box';

                    productDetailsContainer.innerHTML = `
                        <div class="row g-4 align-items-center">
                            <div class="col-md-5">
                                <div class="bg-light rounded-4 p-4 text-center shadow-sm" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                    <i data-feather="${imagePlaceholder}" style="width: 120px; height: 120px; color: var(--primary-color);"></i>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="mb-2">
                                    <span class="badge bg-success px-3 py-2">${product.category}</span>
                                    ${product.organic ? '<span class="badge bg-info px-3 py-2 ms-2">Organic</span>' : ''}
                                </div>
                                <h2 class="fw-bold mb-3">${product.name}</h2>
                                <h3 class="text-success fw-bold mb-4">₱${product.price.toFixed(2)}</h3>
                                <p class="text-secondary">${product.description}</p>

                                <div class="d-flex align-items-center mb-4">
                                    <div class="input-group me-3" style="width: 140px;">
                                        <button class="btn btn-outline-secondary" type="button" id="decrease-quantity">-</button>
                                        <input type="number" class="form-control text-center" id="product-quantity" value="1" min="1">
                                        <button class="btn btn-outline-secondary" type="button" id="increase-quantity">+</button>
                                    </div>
                                    <button class="btn btn-success rounded-pill px-4" id="add-to-cart-btn">
                                        <i data-feather="shopping-cart" class="me-1"></i> Add to Cart
                                    </button>
                                </div>

                                <p><strong>Availability:</strong> ${product.quantity > 0 ? 'In Stock' : 'Out of Stock'}</p>
                                <p><strong>Seller:</strong> ${product.sellerName}</p>
                            </div>
                        </div>
                    `;

                    feather.replace();
                    document.getElementById('decrease-quantity').onclick = () => {
                        let q = document.getElementById('product-quantity');
                        if (q.value > 1) q.value--;
                    };
                    document.getElementById('increase-quantity').onclick = () => {
                        let q = document.getElementById('product-quantity');
                        q.value++;
                    };
                    document.getElementById('add-to-cart-btn').onclick = () => {
                        const quantity = parseInt(document.getElementById('product-quantity').value);
                        if (quantity > 0) addToCart(currentProduct, quantity);
                    };
                })
                .catch(error => {
                    loading.style.display = 'none';
                    productDetailsContainer.innerHTML = `<div class="alert alert-danger rounded-4">Error loading product details. Please try again later.</div>`;
                    console.error(error);
                });
        }
    </script>
</body>
</html>
