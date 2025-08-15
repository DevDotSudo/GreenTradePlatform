<?php
$userType = $_SESSION['user_type'] ?? '';
?>
<header class="header">
    <!-- Top Row: Title and Logout -->
    <div class="header-top">
        <div class="container">
            <div class="header-top-content">
                <h1 class="brand-title">
                    <a href="/" class="brand-link">Green Trade</a>
                </h1>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form id="logout-form" method="POST" action="/logout.php" style="display:inline;">
                        <button type="button" class="btn btn-logout" id="logout-btn" aria-haspopup="dialog" aria-controls="confirm-logout-modal">
                            Logout
                        </button>
                    </form>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a class="btn btn-logout" href="/login.php">Login</a>
                        <a class="btn btn-logout" href="/register.php">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bottom Row: Navigation -->
    <nav class="navbar">
        <div class="container">
            <ul class="nav-menu">
                <?php if ($userType === 'buyer'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/buyer/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>" href="/buyer/products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'cart.php' ? 'active' : ''; ?>" href="/buyer/cart.php">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>" href="/buyer/orders.php">Orders</a>
                    </li>
                <?php elseif ($userType === 'seller'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/seller/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'add_product.php' ? 'active' : ''; ?>" href="/seller/add_product.php">Add Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'my_products.php' ? 'active' : ''; ?>" href="/seller/my_products.php">My Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>" href="/seller/orders.php">Orders</a>
                    </li>
                <?php else: ?>
                    <!-- Admin/Default navigation for non-logged in users -->
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/manage-sellers.php">Manage Sellers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/manage-products.php">Manage Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/orders.php">Orders</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>

<style>
.header {
    background: linear-gradient(90deg, #1b4332, #081c15);
    color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-bottom: 1px solid white;
}

/* Top Row Styles */
.header-top {
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-top-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.brand-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: bold;
}

.brand-link {
    color: white;
    text-decoration: none;
    letter-spacing: 1px;
}

.brand-link:hover {
    color: white;
    opacity: 0.9;
}

.auth-buttons {
    display: flex;
    gap: 10px;
}

/* Bottom Row Styles */
.navbar {
    padding: 8px 0;
}

.nav-menu {
    display: flex;
    align-items: center;
    gap: 0;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    display: flex;
}

.nav-link {
    display: block;
    padding: 8px 15px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: normal;
}

.nav-link:hover {
    color: white;
    background-color: rgba(255, 255, 255, 0.15);
}

.nav-link.active {
    color: white;
    background-color: rgba(255, 255, 255, 0.15);
    font-weight: bold;
    position: relative;
}

.nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: white;
}

.btn-logout {
    background: #4a5568;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.btn-logout:hover {
    background: #2d3748;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

/* Mobile responsive */
@media (max-width: 768px) {
    .header-top-content {
        flex-direction: column;
        gap: 10px;
    }
    
    .nav-menu {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .nav-link {
        padding: 6px 12px;
        font-size: 0.9rem;
    }
    
    .btn-logout {
        padding: 6px 16px;
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .nav-menu {
        flex-direction: column;
        width: 100%;
    }
    
    .nav-item {
        width: 100%;
    }
    
    .nav-link {
        width: 100%;
        text-align: center;
    }
    
    .brand-title {
        font-size: 1.25rem;
    }
}
</style>

<!-- Confirmation modal for logout -->
<div id="confirm-logout-modal" class="confirm-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="confirm-logout-title" style="display:none;">
    <div class="confirm-modal-backdrop" tabindex="-1"></div>
    <div class="confirm-modal-panel" role="document" aria-describedby="confirm-logout-desc">
        <h2 id="confirm-logout-title">Confirm Logout</h2>
        <p id="confirm-logout-desc">Are you sure you want to log out?</p>
        <div class="confirm-modal-actions">
            <button class="btn btn-cancel" id="confirm-cancel">Cancel</button>
            <button class="btn btn-confirm" id="confirm-logout">Logout</button>
        </div>
    </div>
</div>

<style>
/* Confirmation modal styles */
.confirm-modal{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;z-index:9999}
.confirm-modal[aria-hidden="true"]{display:none}
.confirm-modal-backdrop{position:absolute;inset:0;background:rgba(0,0,0,0.45)}
.confirm-modal-panel{position:relative;background:#fff;color:#111;padding:20px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.2);max-width:420px;width:90%;z-index:1}
.confirm-modal-panel h2{margin:0 0 8px 0;font-size:1.1rem}
.confirm-modal-panel p{margin:0 0 16px 0;color:#333}
.confirm-modal-actions{display:flex;justify-content:flex-end;gap:8px}
.btn-cancel{background:#e2e8f0;border:0;padding:8px 12px;border-radius:6px;cursor:pointer}
.btn-confirm{background:#b91c1c;color:#fff;border:0;padding:8px 12px;border-radius:6px;cursor:pointer}
.btn-cancel:hover{filter:brightness(0.95)}
.btn-confirm:hover{filter:brightness(0.95)}
</style>

<script>
(function(){
    var logoutBtn = document.getElementById('logout-btn');
    var modal = document.getElementById('confirm-logout-modal');
    var backdrop = modal && modal.querySelector('.confirm-modal-backdrop');
    var cancel = document.getElementById('confirm-cancel');
    var confirm = document.getElementById('confirm-logout');
    var form = document.getElementById('logout-form');

    function showModal(){
        if(!modal) return;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden','false');
        // move focus to confirm button for keyboard users
        if(confirm) confirm.focus();
    }
    function hideModal(){
        if(!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden','true');
        if(logoutBtn) logoutBtn.focus();
    }

    if(logoutBtn){
        logoutBtn.addEventListener('click', function(e){ e.preventDefault(); showModal(); });
    }
    if(cancel){
        cancel.addEventListener('click', function(e){ e.preventDefault(); hideModal(); });
    }
    if(backdrop){
        backdrop.addEventListener('click', hideModal);
    }
    if(confirm){
        confirm.addEventListener('click', function(e){
            e.preventDefault();
            if(form) form.submit();
            else window.location.href = '/logout.php';
        });
    }
    // close on Escape
    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape' && modal && modal.style.display === 'flex') hideModal();
    });
})();
</script>
