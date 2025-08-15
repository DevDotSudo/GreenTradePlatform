<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <!-- Brand Info -->
            <div class="footer-section">
                <h4 class="footer-title">
                    <span class="footer-icon">🌱</span>
                    Green Trade
                </h4>
                <p class="footer-description">
                    Connecting farmers and buyers for fresh agricultural products.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h5 class="footer-subtitle">Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="/" class="footer-link">Home</a></li>
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'buyer'): ?>
                        <li><a href="/buyer/products.php" class="footer-link">Products</a></li>
                        <li><a href="/buyer/cart.php" class="footer-link">Cart</a></li>
                        <li><a href="/buyer/orders.php" class="footer-link">Orders</a></li>
                    <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'seller'): ?>
                        <li><a href="/seller/add_product.php" class="footer-link">Add Product</a></li>
                        <li><a href="/seller/my_products.php" class="footer-link">My Products</a></li>
                        <li><a href="/seller/orders.php" class="footer-link">Orders</a></li>
                    <?php else: ?>
                        <li><a href="/login.php" class="footer-link">Login</a></li>
                        <li><a href="/register.php" class="footer-link">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-section">
                <h5 class="footer-subtitle">Contact</h5>
                <div class="footer-contact">
                    <p class="contact-item">
                        <span class="contact-icon">📧</span>
                        info@greentrade.com
                    </p>
                    <p class="contact-item">
                        <span class="contact-icon">📞</span>
                        +123 456 7890
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p class="footer-copyright">
                &copy; <?php echo date('Y'); ?> Green Trade. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<style>
.footer {
    background: linear-gradient(90deg, #1b4332, #081c15);
    color: white;
    padding: var(--space-12) 0 var(--space-6);
    margin-top: var(--space-12);
}

.footer-content {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: var(--space-8);
    margin-bottom: var(--space-8);
}

.footer-section {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.footer-title {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: white;
}

.footer-icon {
    font-size: 2rem;
}

.footer-description {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    margin: 0;
}

.footer-subtitle {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
    color: white;
}

.footer-links {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.footer-link {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.footer-link:hover {
    color: white;
    transform: translateX(4px);
}

.footer-contact {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.contact-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
}

.contact-icon {
    font-size: 1.25rem;
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: var(--space-6);
    text-align: center;
}

.footer-copyright {
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
    font-size: 0.875rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer {
        padding: var(--space-8) 0 var(--space-4);
    }
    
    .footer-content {
        grid-template-columns: 1fr;
        gap: var(--space-6);
    }
    
    .footer-section {
        text-align: center;
    }
    
    .footer-links {
        align-items: center;
    }
    
    .contact-item {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .footer {
        padding: var(--space-6) 0 var(--space-4);
    }
    
    .footer-title {
        font-size: 1.25rem;
    }
    
    .footer-icon {
        font-size: 1.5rem;
    }
}
</style>
