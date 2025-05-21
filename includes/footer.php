<footer class="bg-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Green Trade</h5>
                <p class="text-muted">Connecting farmers and buyers for fresh agricultural products.</p>
            </div>
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="/" class="text-decoration-none text-muted">Home</a></li>
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'buyer'): ?>
                        <li><a href="/buyer/products.php" class="text-decoration-none text-muted">Products</a></li>
                        <li><a href="/buyer/cart.php" class="text-decoration-none text-muted">Cart</a></li>
                        <li><a href="/buyer/orders.php" class="text-decoration-none text-muted">Orders</a></li>
                    <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'seller'): ?>
                        <li><a href="/seller/add_product.php" class="text-decoration-none text-muted">Add Product</a></li>
                        <li><a href="/seller/my_products.php" class="text-decoration-none text-muted">My Products</a></li>
                        <li><a href="/seller/orders.php" class="text-decoration-none text-muted">Orders</a></li>
                    <?php else: ?>
                        <li><a href="/login.php" class="text-decoration-none text-muted">Login</a></li>
                        <li><a href="/register.php" class="text-decoration-none text-muted">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contact</h5>
                <address class="text-muted">
                    <p>Email: info@greentrade.com</p>
                    <p>Phone: +123 456 7890</p>
                </address>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> Green Trade. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
