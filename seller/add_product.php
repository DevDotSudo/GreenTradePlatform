<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';

ensureUserLoggedIn('seller');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid px-3 px-md-4 mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h1 class="h2 mb-0">Add New Product</h1>
            <a href="my_products.php" class="btn btn-outline-success">
                <i data-feather="arrow-left" class="me-1"></i> Back to My Products
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="addProductForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label for="productName" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="productName" name="productName" required maxlength="100">
                                <div class="invalid-feedback">Please enter a product name</div>
                            </div>

                            <div class="mb-3">
                                <label for="productDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="productDescription" name="productDescription" rows="4" maxlength="500"></textarea>
                                <div class="form-text">Optional description for your product</div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="productPrice" class="form-label">Price (₱) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="productPrice" name="productPrice" min="0.01" max="99999.99" step="0.01" required>
                                    <div class="invalid-feedback">Please enter a valid price</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="productQuantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="productQuantity" name="productQuantity" min="1" max="99999" required>
                                    <div class="invalid-feedback">Please enter a valid quantity</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="productUnit" class="form-label">Unit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="productUnit" name="productUnit" placeholder="e.g., kg, packs, pieces, dozen" required>
                                    <div class="invalid-feedback">Please specify a unit (e.g., kg, packs, pieces)</div>
                                    <div class="form-text">Specify the unit for your product (kg, packs, pieces, dozen, etc.)</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="productCategory" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="productCategory" name="productCategory" required>
                                        <option value="">Select a category</option>
                                        <option value="Vegetables">Vegetables</option>
                                        <option value="Fruits">Fruits</option>
                                        <option value="Rice">Rice</option>
                                        <option value="Fish">Fish</option>
                                        <option value="Meat">Meat</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a category</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">&nbsp;</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="isOrganic" name="isOrganic">
                                        <label class="form-check-label" for="isOrganic">
                                            This product is organic
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3" id="specificNameContainer" style="display: none;">
                                <label for="specificName" class="form-label">Specific Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="specificName" name="specificName" maxlength="100" placeholder="e.g., Dairy Milk">
                                <div class="invalid-feedback">Please enter a specific name for the Other category</div>
                                <div class="form-text">Specify the type of product for the Other category</div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="productImage" class="form-label">Product Image</label>
                                <div class="border border-dashed rounded p-4 text-center" id="imageDropZone">
                                    <img id="imagePreview" src="" alt="Preview" style="max-width: 100%; max-height: 200px; display: none;">
                                    <div id="imagePlaceholder">
                                        <i data-feather="image" style="width: 48px; height: 48px; color: #ccc;"></i>
                                        <p class="mt-2 mb-1">Click to upload image</p>
                                        <small class="text-muted">JPG, PNG or GIF (Max: 2MB)</small>
                                    </div>
                                </div>
                                <input type="file" class="form-control d-none" id="productImage" name="productImage" accept="image/jpeg,image/png,image/gif">
                                <div class="form-text">Optional product image</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="my_products.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" id="loadingSpinner" role="status"></span>
                            <span id="submitText">Add Product</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">
                        <i data-feather="check-circle" class="me-2"></i>Product Added Successfully
                    </h5>
                </div>
                <div class="modal-body">
                    <p>Your product has been added successfully!</p>
                    <p class="mb-0">Would you like to add another product or view all products?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="addAnotherBtn">Add Another</button>
                    <a href="my_products.php" class="btn btn-outline-success">View Products</a>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();

            // Get form elements
            const form = document.getElementById('addProductForm');
            const productName = document.getElementById('productName');
            const productDescription = document.getElementById('productDescription');
            const productPrice = document.getElementById('productPrice');
            const productQuantity = document.getElementById('productQuantity');
            const productUnit = document.getElementById('productUnit');
            const productCategory = document.getElementById('productCategory');
            const specificName = document.getElementById('specificName');
            const specificNameContainer = document.getElementById('specificNameContainer');
            const isOrganic = document.getElementById('isOrganic');
            const productImage = document.getElementById('productImage');
            const imageDropZone = document.getElementById('imageDropZone');
            const imagePreview = document.getElementById('imagePreview');
            const imagePlaceholder = document.getElementById('imagePlaceholder');
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const submitText = document.getElementById('submitText');
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));

            // Category change handler
            productCategory.addEventListener('change', function() {
                if (this.value === 'Other') {
                    specificNameContainer.style.display = 'block';
                    specificName.required = true;
                } else {
                    specificNameContainer.style.display = 'none';
                    specificName.required = false;
                    specificName.value = '';
                    specificName.classList.remove('is-invalid');
                }
            });

            // Image upload functionality
            imageDropZone.addEventListener('click', () => productImage.click());
            imageDropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                imageDropZone.classList.add('border-primary');
            });
            imageDropZone.addEventListener('dragleave', () => {
                imageDropZone.classList.remove('border-primary');
            });
            imageDropZone.addEventListener('drop', async (e) => {
                e.preventDefault();
                imageDropZone.classList.remove('border-primary');

                // Check for files first
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleImageUpload(files[0]);
                    return;
                }

                // Check for URLs (dragged from website)
                const url = e.dataTransfer.getData('text/uri-list') || e.dataTransfer.getData('text/plain');
                if (url) {
                    try {
                        // Fetch the image from URL
                        const response = await fetch(url);
                        const blob = await response.blob();

                        // Create a File object from the blob
                        const file = new File([blob], 'dragged-image.jpg', { type: blob.type });
                        handleImageUpload(file);
                    } catch (error) {
                        console.error('Error fetching dragged image:', error);
                        showAlert('Failed to load image from URL. Please try uploading a file instead.');
                    }
                }
            });

            productImage.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleImageUpload(e.target.files[0]);
                }
            });

            async function handleImageUpload(file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    showAlert('Invalid file type. Please select a JPG, PNG, or GIF file.');
                    return;
                }

                // Validate original file size (3MB)
                if (file.size > 3 * 1024 * 1024) {
                    showAlert('File size too large. Please select an image smaller than 3MB.');
                    return;
                }

                try {
                    // Compress image before base64 encoding
                    const compressedDataUrl = await compressImage(file);
                    imagePreview.src = compressedDataUrl;
                    imagePreview.style.display = 'block';
                    imagePlaceholder.style.display = 'none';
                } catch (error) {
                    console.error('Image compression failed:', error);
                    showAlert('Failed to process image. Please try a different file.');
                }
            }

            function compressImage(file, maxWidth = 800, quality = 0.8) {
                return new Promise((resolve, reject) => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const img = new Image();
                    
                    img.onload = () => {
                        // Calculate new dimensions
                        let { width, height } = img;
                        if (width > maxWidth) {
                            height = (height * maxWidth) / width;
                            width = maxWidth;
                        }
                        
                        canvas.width = width;
                        canvas.height = height;
                        
                        // Draw and compress
                        ctx.drawImage(img, 0, 0, width, height);
                        
                        try {
                            // Convert to base64 with compression
                            const dataUrl = canvas.toDataURL('image/jpeg', quality);
                            
                            // Check if compressed size is still too large (less than 900KB)
                            if (dataUrl.length > 900000) {
                                reject(new Error('Image too large even after compression'));
                                return;
                            }
                            
                            resolve(dataUrl);
                        } catch (error) {
                            reject(error);
                        }
                    };
                    
                    img.onerror = () => reject(new Error('Failed to load image'));
                    img.src = URL.createObjectURL(file);
                });
            }

            // Form validation
            function validateForm() {
                let isValid = true;

                // Clear previous validation states
                [productName, productPrice, productQuantity, productUnit, productCategory, specificName].forEach(input => {
                    input.classList.remove('is-invalid');
                });

                // Validate required fields
                if (!productName.value.trim()) {
                    productName.classList.add('is-invalid');
                    isValid = false;
                }

                const price = parseFloat(productPrice.value);
                if (!productPrice.value || isNaN(price) || price <= 0) {
                    productPrice.classList.add('is-invalid');
                    isValid = false;
                }

                const quantity = parseInt(productQuantity.value);
                if (!productQuantity.value || isNaN(quantity) || quantity < 1) {
                    productQuantity.classList.add('is-invalid');
                    isValid = false;
                }

                if (!productUnit.value.trim()) {
                    productUnit.classList.add('is-invalid');
                    isValid = false;
                }

                if (!productCategory.value) {
                    productCategory.classList.add('is-invalid');
                    isValid = false;
                }

                if (productCategory.value === 'Other' && !specificName.value.trim()) {
                    specificName.classList.add('is-invalid');
                    isValid = false;
                }

                return isValid;
            }

            // Form submission
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                if (!validateForm()) {
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                loadingSpinner.classList.remove('d-none');
                submitText.textContent = 'Adding...';

                try {
                    // Wait for Firebase
                    await new Promise((resolve, reject) => {
                        const timeout = setTimeout(() => reject(new Error('Firebase timeout')), 10000);
                        window.waitForFirebase(() => {
                            clearTimeout(timeout);
                            resolve();
                        });
                    });

                    // Create product data
                    const productData = {
                        name: productName.value.trim(),
                        description: productDescription.value.trim(),
                        price: parseFloat(productPrice.value),
                        quantity: parseInt(productQuantity.value),
                        unit: productUnit.value.trim(),
                        category: productCategory.value,
                        organic: isOrganic.checked,
                        sellerId: '<?php echo $_SESSION['user_id']; ?>',
                        sellerName: '<?php echo htmlspecialchars($_SESSION['name']); ?>',
                        createdAt: new Date(),
                        status: 'active'
                    };

                    // Add specific name for Other category
                    if (productCategory.value === 'Other') {
                        productData.specificName = specificName.value.trim();
                    }

                    // Handle image upload if present
                    if (productImage.files.length > 0) {
                        const file = productImage.files[0];
                        try {
                            // Compress image before storing
                            const compressedImageData = await compressImage(file, 600, 0.7); // Smaller size, lower quality
                            productData.imageData = compressedImageData;
                        } catch (error) {
                            console.error('Image compression failed during submission:', error);
                            showAlert('Failed to process image. Product will be saved without image.', 'warning');
                        }
                    }

                    // Save to Firestore
                    const docRef = await firebase.firestore().collection('products').add(productData);
                    
                    console.log('Product added with ID:', docRef.id);
                    
                    // Show success modal
                    successModal.show();
                    
                    // Handle success modal buttons
                    document.getElementById('addAnotherBtn').addEventListener('click', () => {
                        successModal.hide();
                        form.reset();
                        imagePreview.style.display = 'none';
                        imagePlaceholder.style.display = 'block';
                        specificNameContainer.style.display = 'none';
                        specificName.required = false;
                        [productName, productPrice, productQuantity, productUnit, productCategory, specificName].forEach(input => {
                            input.classList.remove('is-invalid');
                        });
                    });

                } catch (error) {
                    console.error('Error adding product:', error);
                    showAlert('Failed to add product. Please try again.', 'error');
                } finally {
                    // Reset button state
                    submitBtn.disabled = false;
                    loadingSpinner.classList.add('d-none');
                    submitText.textContent = 'Add Product';
                }
            });

            // Helper function to show alerts
            function showAlert(message, type = 'warning') {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
                alertDiv.style.top = '20px';
                alertDiv.style.right = '20px';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alertDiv);
                
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        });
    </script>

    <style>
        .border-dashed {
            border-style: dashed !important;
            border-color: #dee2e6;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .border-dashed:hover {
            border-color: #0d6efd;
        }
        
        .border-dashed.border-primary {
            border-color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.05);
        }

        .modal-header {
            background-color: #d1e7dd;
            border-bottom: none;
        }

        .text-success {
            color: #198754 !important;
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>
</body>
</html>