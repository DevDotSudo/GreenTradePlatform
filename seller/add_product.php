<?php
session_start();
include '../includes/auth.php';
include '../includes/functions.php';

// Ensure user is logged in as a seller
ensureUserLoggedIn('seller');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Add New Product</h1>
            <a href="my_products.php" class="btn btn-outline-success">
                <i data-feather="arrow-left" class="me-1"></i> Back to My Products
            </a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form id="add-product-form">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="4"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (₱)<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="price" min="0.01" step="0.01" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity Available<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" min="1" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category<span class="text-danger">*</span></label>
                        <select class="form-select" id="category" required>
                            <option value="" selected disabled>Select category</option>
                            <option value="Vegetables">Vegetables</option>
                            <option value="Fruits">Fruits</option>
                            <option value="Rice">Rice</option>
                            <option value="Fish">Fish</option>
                            <option value="Meat">Meat</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="organic">
                            <label class="form-check-label" for="organic">
                                This product is organic
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <div class="border rounded p-3 text-center" style="position: relative;">
                            <div id="image-preview" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                <div class="text-muted">
                                    <i data-feather="image" style="width: 48px; height: 48px;"></i>
                                    <p class="mt-2">Drag your image here or click to select</p>
                                    <p class="small">Max file size: 5MB. Accepted formats: JPG, JPEG, PNG</p>
                                </div>
                            </div>
                            <input type="file" id="image" accept="image/*" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                        </div>
                        <div class="text-danger small mt-1" id="image-error"></div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    
    <!-- Added Bootstrap bundle for alerts/modals -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();
            
            // Image preview functionality
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('image-preview');
            const imageError = document.getElementById('image-error');
            
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                
                // Validate file size and type
                if (file) {
                    const fileSize = file.size / 1024 / 1024; // size in MB
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    
                    if (fileSize > 5) {
                        imageError.textContent = 'File size exceeds 5MB limit';
                        this.value = ''; // Clear the input
                        return;
                    }
                    
                    if (!validTypes.includes(file.type)) {
                        imageError.textContent = 'Only JPG, JPEG, and PNG files are allowed';
                        this.value = ''; // Clear the input
                        return;
                    }
                    
                    // Clear any previous errors
                    imageError.textContent = '';
                    
                    // Show image preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.innerHTML = `
                            <img src="${e.target.result}" alt="Product preview" style="max-height: 200px; max-width: 100%;">
                        `;
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Create alert toast function
            function showToast(title, message, type = 'success') {
                // Create the toast element
                const toast = document.createElement('div');
                toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed`;
                toast.style.top = '20px';
                toast.style.right = '20px';
                toast.style.zIndex = '1100';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>${title}</strong><br>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;
                
                document.body.appendChild(toast);
                
                // Create Bootstrap toast instance and show it
                const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
                bsToast.show();
                
                // Remove from DOM after hiding
                toast.addEventListener('hidden.bs.toast', function() {
                    document.body.removeChild(toast);
                });
            }
            
            // Create confirmation modal
            function showConfirmation(title, message, onConfirm) {
                // Create modal element
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'confirmationModal';
                modal.setAttribute('tabindex', '-1');
                modal.setAttribute('aria-labelledby', 'confirmationModalLabel');
                modal.setAttribute('aria-hidden', 'true');
                
                modal.innerHTML = `
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="confirmationModalLabel">${title}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${message}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-success" id="confirmBtn">Confirm</button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Create and show Bootstrap modal
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                
                // Set up confirm button
                document.getElementById('confirmBtn').addEventListener('click', function() {
                    bsModal.hide();
                    onConfirm();
                });
                
                // Remove from DOM after hiding
                modal.addEventListener('hidden.bs.modal', function() {
                    document.body.removeChild(modal);
                });
            }
            
            // Form submission
            const addProductForm = document.getElementById('add-product-form');
            
            addProductForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form values
                const name = document.getElementById('name').value.trim();
                const description = document.getElementById('description').value.trim();
                const price = parseFloat(document.getElementById('price').value);
                const quantity = parseInt(document.getElementById('quantity').value);
                const category = document.getElementById('category').value;
                const organic = document.getElementById('organic').checked;
                
                // Validate form
                if (!name || !price || !quantity || !category) {
                    showToast('Missing Information', 'Please fill in all required fields.', 'danger');
                    return;
                }
                
                // Show confirmation modal
                const confirmMessage = `
                    <p>Please confirm the following product details:</p>
                    <ul class="mb-0">
                        <li><strong>Name:</strong> ${name}</li>
                        <li><strong>Price:</strong> ₱${price.toFixed(2)}</li>
                        <li><strong>Category:</strong> ${category}</li>
                        <li><strong>Quantity:</strong> ${quantity}</li>
                        <li><strong>Organic:</strong> ${organic ? 'Yes' : 'No'}</li>
                    </ul>
                `;
                
                showConfirmation('Confirm Product Addition', confirmMessage, function() {
                    // Disable submit button to prevent multiple submissions
                    const submitButton = addProductForm.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding Product...';
                    
                    // Process image if present
                    const imageFile = document.getElementById('image').files[0];
                    const processImageAndSaveProduct = function() {
                        // Create base product object
                        const product = {
                            name: name,
                            description: description || '',
                            price: price,
                            quantity: quantity,
                            category: category,
                            organic: organic,
                            sellerId: '<?php echo $_SESSION['user_id']; ?>',
                            sellerName: '<?php echo htmlspecialchars($_SESSION['name']); ?>',
                            createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                            updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                        };
                        
                        // Add product to Firestore
                        firebase.firestore().collection('products').add(product)
                            .then(docRef => {
                                showToast('Success', 'Product has been added successfully!', 'success');
                                // Redirect to product list page after a short delay
                                setTimeout(() => {
                                    window.location.href = 'my_products.php?added=' + docRef.id;
                                }, 1500);
                            })
                            .catch(error => {
                                console.error("Error adding product: ", error);
                                showToast('Error', 'Failed to add product. Please try again.', 'danger');
                                
                                // Re-enable submit button
                                submitButton.disabled = false;
                                submitButton.textContent = 'Add Product';
                            });
                    };
                    
                    if (imageFile) {
                        // Convert image to base64
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Process the base64 image
                            const base64Image = e.target.result;
                            
                            // Check if image size is not too large for Firestore
                            if (base64Image.length > 10485760) { // 10MB limit
                                showToast('Image Too Large', 'The image is too large to store. Please use a smaller image.', 'warning');
                                submitButton.disabled = false;
                                submitButton.textContent = 'Add Product';
                                return;
                            }
                            
                            // Create product object with image
                            const product = {
                                name: name,
                                description: description || '',
                                price: price,
                                quantity: quantity,
                                category: category,
                                organic: organic,
                                sellerId: '<?php echo $_SESSION['user_id']; ?>',
                                sellerName: '<?php echo htmlspecialchars($_SESSION['name']); ?>',
                                imageData: base64Image, // Store image as base64
                                createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                                updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                            };
                            
                            // Add to Firestore
                            firebase.firestore().collection('products').add(product)
                                .then(docRef => {
                                    showToast('Success', 'Product has been added successfully!', 'success');
                                    // Redirect to product list page after a short delay
                                    setTimeout(() => {
                                        window.location.href = 'my_products.php?added=' + docRef.id;
                                    }, 1500);
                                })
                                .catch(error => {
                                    console.error("Error adding product: ", error);
                                    showToast('Error', 'Failed to add product. Please try again.', 'danger');
                                    
                                    // Re-enable submit button
                                    submitButton.disabled = false;
                                    submitButton.textContent = 'Add Product';
                                });
                        };
                        
                        reader.onerror = function() {
                            showToast('Image Error', 'Failed to process the image. Please try again.', 'danger');
                            submitButton.disabled = false;
                            submitButton.textContent = 'Add Product';
                        };
                        
                        reader.readAsDataURL(imageFile);
                    } else {
                        // No image to process
                        processImageAndSaveProduct();
                    }
                });
            });
        });
    </script>
</body>
</html>
