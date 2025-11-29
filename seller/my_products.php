<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';
include '../includes/functions.php';

ensureUserLoggedIn('seller');

$added = isset($_GET['added']) ? $_GET['added'] : '';
$updated = isset($_GET['updated']) ? $_GET['updated'] : '';
$deleted = isset($_GET['deleted']) ? $_GET['deleted'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid px-3 px-md-4 mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h1 class="h2 mb-0">My Products</h1>
            <a href="add_product.php" class="btn btn-success">
                <i data-feather="plus" class="me-1"></i> Add New Product
            </a>
        </div>

        <?php if ($added): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Product added successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($updated): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Product updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($deleted): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Product deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="products-section">
            <div class="products-container">
                <div id="products-container">
                    <!-- Products will be loaded here -->
                </div>

                <div class="empty-state d-none" id="no-products">
                    <div class="empty-icon">📦</div>
                    <h3 class="empty-title">No products yet</h3>
                    <p class="empty-description">You haven't added any products yet. Start selling by adding your first product.</p>
                    <a href="add_product.php" class="btn btn-success">Add Your First Product</a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteProductModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this product? This action cannot be undone.</p>
                    <p><strong id="delete-product-name"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/dialogs.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/products.js"></script>
    <style>
        .container-fluid {
            max-width: 1400px;
        }

        .products-section {
            background: white;
            border-radius: var(--radius-xl, 12px);
            box-shadow: var(--shadow-md, 0 4px 6px -1px rgba(0, 0, 0, 0.1));
            border: 1px solid var(--gray-200, #e5e7eb);
            overflow: hidden;
        }

        .products-container {
            padding: var(--space-6, 1.5rem);
        }

        .empty-state {
            text-align: center;
            padding: var(--space-12, 3rem) var(--space-6, 1.5rem);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: var(--space-4, 1rem);
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900, #111827);
            margin-bottom: var(--space-2, 0.5rem);
        }

        .empty-description {
            color: var(--gray-600, #6b7280);
            margin-bottom: var(--space-6, 1.5rem);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: var(--gray-50, #f9fafb);
            border-bottom: 2px solid var(--gray-200, #e5e7eb);
            font-weight: 600;
            color: var(--gray-900, #111827);
            padding: var(--space-4, 1rem) var(--space-3, 0.75rem);
        }

        .table td {
            padding: var(--space-4, 1rem) var(--space-3, 0.75rem);
            vertical-align: middle;
        }

        .product-image {
            width: 90px;
            height: 90px;
            background: var(--gray-100, #f3f4f6);
            border-radius: var(--radius-lg, 8px);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid var(--gray-200, #e5e7eb);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .product-info h6 {
            margin: 0 0 var(--space-1, 0.25rem) 0;
            font-weight: 600;
            color: var(--gray-900, #111827);
        }

        .product-description {
            font-size: 0.875rem;
            color: var(--gray-600, #6b7280);
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .badge {
            font-size: 0.75rem;
            padding: var(--space-1, 0.25rem) var(--space-2, 0.5rem);
            border-radius: var(--radius-full, 9999px);
        }

        .btn-group .btn {
            padding: var(--space-2, 0.5rem) var(--space-3, 0.75rem);
            font-size: 0.875rem;
        }

        .btn-group .btn i {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding-left: var(--space-3, 0.75rem);
                padding-right: var(--space-3, 0.75rem);
            }

            .d-flex {
                flex-direction: column;
                align-items: stretch !important;
            }

            .btn {
                width: 100%;
                margin-bottom: var(--space-2, 0.5rem);
            }

            .table-responsive {
                font-size: 0.875rem;
            }

            .product-image {
                width: 80px;
                height: 80px;
            }

            .product-info h6 {
                font-size: 1rem;
            }

            .btn-group {
                flex-direction: column;
                width: 100%;
            }

            .btn-group .btn {
                width: 100%;
                justify-content: center;
                margin-bottom: var(--space-1, 0.25rem);
            }
        }

        /* Products Grid Layout */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: var(--space-6, 1.5rem);
        }

        .product-card {
            background: white;
            border-radius: var(--radius-lg, 8px);
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0, 0, 0, 0.1));
            border: 1px solid var(--gray-200, #e5e7eb);
            overflow: hidden;
            transition: all var(--transition-normal, 0.3s ease);
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md, 0 4px 6px -1px rgba(0, 0, 0, 0.1));
        }

        .product-image-container {
            position: relative;
            width: 100%;
            height: 200px;
            background: var(--gray-100, #f3f4f6);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image-large {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-image-placeholder {
            color: var(--gray-400, #9ca3af);
        }

        .product-image-placeholder i {
            width: 48px;
            height: 48px;
        }

        .product-details {
            padding: var(--space-4, 1rem);
        }

        .product-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900, #111827);
            margin-bottom: var(--space-2, 0.5rem);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-description {
            color: var(--gray-600, #6b7280);
            font-size: 0.875rem;
            margin-bottom: var(--space-3, 0.75rem);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-meta {
            margin-bottom: var(--space-4, 1rem);
        }

        .product-category {
            margin-bottom: var(--space-2, 0.5rem);
        }

        .product-price {
            font-size: 1.25rem;
            margin-bottom: var(--space-2, 0.5rem);
        }

        .product-quantity {
            margin-bottom: var(--space-2, 0.5rem);
        }

        .product-actions {
            display: flex;
            gap: var(--space-2, 0.5rem);
            flex-wrap: wrap;
        }

        .product-actions .btn {
            flex: 1;
            min-width: 80px;
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: var(--space-4, 1rem);
            }

            .product-image-container {
                height: 160px;
            }

            .product-details {
                padding: var(--space-3, 0.75rem);
            }

            .product-actions {
                flex-direction: column;
            }

            .product-actions .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .products-container {
                padding: var(--space-4, 1rem);
            }

            .products-grid {
                grid-template-columns: 1fr;
                gap: var(--space-3, 0.75rem);
            }

            .empty-state {
                padding: var(--space-8, 2rem) var(--space-4, 1rem);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            waitForFirebase(() => {
                loadSellerProducts();
            });
        });

        let currentProductId = null;

        function loadSellerProducts() {
            const sellerId = '<?php echo $_SESSION['user_id']; ?>';
            const productsContainer = document.getElementById('products-container');
            const noProducts = document.getElementById('no-products');

            if (window.loadingOverlay) {
                window.loadingOverlay.show('Loading products...');
            }

            firebase.firestore().collection('products')
                .where('sellerId', '==', sellerId)
                .orderBy('createdAt', 'desc')
                .get()
                .then(snapshot => {
                    if (window.loadingOverlay) window.loadingOverlay.hide();

                    if (snapshot.empty) {
                        noProducts.classList.remove('d-none');
                        return;
                    }

                    // Create products grid layout
                    const productsGrid = document.createElement('div');
                    productsGrid.className = 'products-grid';

                    snapshot.forEach(doc => {
                        const product = {
                            id: doc.id,
                            ...doc.data()
                        };

                        const productCard = document.createElement('div');
                        productCard.className = 'product-card';

                        // Check if product has base64 image data
                        const hasImage = product.imageData ? true : false;

                        productCard.innerHTML = `
                            <div class="product-image-container">
                                ${hasImage ?
                                    `<img src="${product.imageData}" alt="${product.name}" class="product-image-large">` :
                                    `<div class="product-image-placeholder"><i data-feather="package"></i></div>`
                                }
                            </div>
                            <div class="product-details">
                                <h5 class="product-name">${product.name}</h5>
                                <p class="product-description">${product.description ? product.description.substring(0, 100) + (product.description.length > 100 ? '...' : '') : ''}</p>
                                <div class="product-meta">
                                    <div class="product-category">
                                        <span class="badge ${product.organic ? 'bg-success' : 'bg-secondary'}">${product.category === 'Other' && product.specificName ? product.specificName : product.category}</span>
                                        ${product.organic ? '<span class="badge bg-info ms-1">Organic</span>' : ''}
                                    </div>
                                    <div class="product-price">
                                        <strong class="text-success">₱${product.price.toFixed(2)}</strong>
                                    </div>
                                    <div class="product-quantity">
                                        <small class="text-muted">Qty: ${product.quantity || 0}</small>
                                    </div>
                                    <div class="product-unit">
                                        <small class="text-muted">Unit: ${product.unit || 'kg'}</small>
                                    </div>
                                </div>
                                <div class="product-actions">
                                    <a href="edit_product.php?id=${product.id}" class="btn btn-outline-primary btn-sm">
                                        <i data-feather="edit"></i> Edit
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm delete-product" data-id="${product.id}" data-name="${product.name}">
                                        <i data-feather="trash-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        `;

                        productsGrid.appendChild(productCard);
                    });

                    productsContainer.innerHTML = '';
                    productsContainer.appendChild(productsGrid);

                    // Initialize Feather icons for dynamically added content
                    feather.replace();

                    // Add event listeners for delete buttons
                    document.querySelectorAll('.delete-product').forEach(button => {
                        button.addEventListener('click', function() {
                            const productId = this.getAttribute('data-id');
                            const productName = this.getAttribute('data-name');

                            // Set current product for delete modal
                            currentProductId = productId;

                            // Update modal content
                            document.getElementById('delete-product-name').textContent = productName;

                            // Show the modal
                            const modal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
                            modal.show();
                        });
                    });

                    // Set up confirm delete button
                    document.getElementById('confirm-delete').addEventListener('click', function() {
                        if (currentProductId) {
                            deleteProduct(currentProductId);
                        }
                    });
                })
                .catch(error => {
                    if (window.loadingOverlay) window.loadingOverlay.hide();
                    console.error("Error getting products: ", error);

                    productsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            Error loading products. Please try again later.
                        </div>
                    `;
                });
        }

        // Function to delete a product
        function deleteProduct(productId) {
            // Disable delete button to prevent multiple clicks
            const deleteButton = document.getElementById('confirm-delete');
            deleteButton.disabled = true;
            deleteButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';

            // Show loading overlay
            if (window.loadingOverlay) {
                window.loadingOverlay.show('Deleting product...');
            }

            // Delete the product from Firestore
            firebase.firestore().collection('products').doc(productId).delete()
                .then(() => {
                    // Hide the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteProductModal'));
                    modal.hide();

                    // Redirect to products page with deleted parameter
                    window.location.href = 'my_products.php?deleted=true';
                })
                .catch(error => {
                    console.error("Error deleting product: ", error);
                    showAlert({
                        title: 'Delete Failed',
                        message: 'Error deleting product. Please try again later.',
                        type: 'error'
                    });

                    // Re-enable delete button
                    deleteButton.disabled = false;
                    deleteButton.textContent = 'Delete';

                    // Hide loading overlay
                    if (window.loadingOverlay) {
                        window.loadingOverlay.hide();
                    }
                });
        }
    </script>
</body>

</html>