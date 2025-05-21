<?php
session_start();
include '../includes/auth.php';
include '../includes/functions.php';

// Ensure user is logged in as a seller
ensureUserLoggedIn('seller');

// Check for success message
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Products</h1>
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
        
        <div class="card">
            <div class="card-body">
                <div id="products-container">
                    <!-- Products will be loaded here -->
                    <div class="text-center py-5" id="loading">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading your products...</p>
                    </div>
                    
                    <div class="text-center py-5 d-none" id="no-products">
                        <i data-feather="package" style="width: 48px; height: 48px;" class="text-muted mb-3"></i>
                        <h4>No products yet</h4>
                        <p class="text-muted">You haven't added any products yet. Start selling by adding your first product.</p>
                        <a href="add_product.php" class="btn btn-success mt-2">Add Your First Product</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Product Modal -->
    <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
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
    
    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-storage-compat.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/products.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();
            
            // Load seller's products
            loadSellerProducts();
        });
        
        // Current product for delete modal
        let currentProductId = null;
        
        // Function to load seller's products
        function loadSellerProducts() {
            const sellerId = '<?php echo $_SESSION['user_id']; ?>';
            const productsContainer = document.getElementById('products-container');
            const loading = document.getElementById('loading');
            const noProducts = document.getElementById('no-products');
            
            // Get products from Firestore
            firebase.firestore().collection('products')
                .where('sellerId', '==', sellerId)
                .orderBy('createdAt', 'desc')
                .get()
                .then(snapshot => {
                    loading.style.display = 'none';
                    
                    if (snapshot.empty) {
                        noProducts.classList.remove('d-none');
                        return;
                    }
                    
                    // Create table to display products
                    const table = document.createElement('table');
                    table.className = 'table table-striped table-hover align-middle';
                    
                    const tableHead = document.createElement('thead');
                    tableHead.innerHTML = `
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    `;
                    
                    const tableBody = document.createElement('tbody');
                    
                    snapshot.forEach(doc => {
                        const product = {
                            id: doc.id,
                            ...doc.data()
                        };
                        
                        const row = document.createElement('tr');
                        
                        // Check if product has base64 image data
                        const hasImage = product.imageData ? true : false;
                        
                        row.innerHTML = `
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded text-center p-2 me-3" style="width: 60px; height: 60px; overflow: hidden;">
                                        ${hasImage ? 
                                            `<img src="${product.imageData}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover;">` : 
                                            `<i data-feather="package" style="width: 24px; height: 24px;"></i>`
                                        }
                                    </div>
                                    <div>
                                        <h6 class="mb-0">${product.name}</h6>
                                        <small class="text-muted">${product.description ? product.description.substring(0, 50) + (product.description.length > 50 ? '...' : '') : ''}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge ${product.organic ? 'bg-success' : 'bg-secondary'}">${product.category}</span>
                                ${product.organic ? '<span class="badge bg-info ms-1">Organic</span>' : ''}
                            </td>
                            <td>₱${product.price.toFixed(2)}</td>
                            <td>${product.quantity}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit_product.php?id=${product.id}" class="btn btn-sm btn-outline-primary">
                                        <i data-feather="edit" class="feather-sm"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger delete-product" data-id="${product.id}" data-name="${product.name}">
                                        <i data-feather="trash-2" class="feather-sm"></i>
                                    </button>
                                </div>
                            </td>
                        `;
                        
                        tableBody.appendChild(row);
                    });
                    
                    table.appendChild(tableHead);
                    table.appendChild(tableBody);
                    productsContainer.innerHTML = '';
                    productsContainer.appendChild(table);
                    
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
                    loading.style.display = 'none';
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
                    alert('Error deleting product. Please try again later.');
                    
                    // Re-enable delete button
                    deleteButton.disabled = false;
                    deleteButton.textContent = 'Delete';
                });
        }
    </script>
</body>
</html>
