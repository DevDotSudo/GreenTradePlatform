<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';
include '../includes/functions.php';

ensureUserLoggedIn('seller');

$productId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

if (!$productId) {
    header('Location: my_products.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Green Trade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/dialogs.js"></script>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Edit Product</h1>
            <a href="my_products.php" class="btn btn-outline-success">
                <i data-feather="arrow-left" class="me-1"></i> Back to My Products
            </a>
        </div>

        <div class="card">
            <div class="card-body">

                <form id="edit-product-form" style="display: none;">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" maxlength="100" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="4" maxlength="500" placeholder="Describe your product..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Price (₱)<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="price" min="0.01" max="99999.99" step="0.01" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Quantity Available<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" min="1" max="99999" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="unit" class="form-label">Unit<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unit" placeholder="e.g., kg, packs, pieces, dozen" required>
                            <div class="form-text">Specify the unit for your product (kg, packs, pieces, dozen, etc.)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category<span class="text-danger">*</span></label>
                        <select class="form-select" id="category" required>
                            <option value="" disabled>Select category</option>
                            <option value="Vegetables">Vegetables</option>
                            <option value="Fruits">Fruits</option>
                            <option value="Rice">Rice</option>
                            <option value="Fish">Fish</option>
                            <option value="Meat">Meat</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3" id="editSpecificNameContainer" style="display: none;">
                        <label for="editSpecificName" class="form-label">Specific Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editSpecificName" maxlength="100" placeholder="e.g., Dairy Milk">
                        <div class="invalid-feedback">Please enter a specific name for the Other category</div>
                        <div class="form-text">Specify the type of product for the Other category</div>
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
                        <button type="submit" class="btn btn-success">Update Product</button>
                    </div>
                </form>

                <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-storage-compat.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="../assets/js/firebase.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/products.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            waitForFirebase(() => {
                loadProductDetails('<?php echo $productId; ?>');
            });
        });

        function loadProductDetails(productId) {
            const sellerId = '<?php echo $_SESSION['user_id']; ?>';
            const formElement = document.getElementById('edit-product-form');
            const errorElement = document.getElementById('error-message');

            if (window.loadingOverlay) {
                window.loadingOverlay.show('Loading product details...');
            }

            firebase.firestore().collection('products').doc(productId).get()
                .then(doc => {
                    if (window.loadingOverlay) window.loadingOverlay.hide();

                    if (!doc.exists) {
                        errorElement.textContent = 'Product not found.';
                        errorElement.style.display = 'block';
                        return;
                    }

                    const product = doc.data();

                    // Check if this product belongs to the logged-in seller
                    if (product.sellerId !== sellerId) {
                        errorElement.textContent = 'You do not have permission to edit this product.';
                        errorElement.style.display = 'block';
                        return;
                    }

                    // Show the form
                    formElement.style.display = 'block';

                    // Fill the form with product details
                    document.getElementById('name').value = product.name || '';
                    document.getElementById('description').value = product.description || '';
                    document.getElementById('price').value = product.price || 0;
                    document.getElementById('quantity').value = product.quantity || 0;
                    document.getElementById('unit').value = product.unit || '';
                    document.getElementById('category').value = product.category || '';
                    document.getElementById('organic').checked = product.organic || false;

                    // Handle specific name for Other category
                    const editSpecificName = document.getElementById('editSpecificName');
                    const editSpecificNameContainer = document.getElementById('editSpecificNameContainer');
                    if (product.category === 'Other') {
                        editSpecificNameContainer.style.display = 'block';
                        editSpecificName.value = product.specificName || '';
                        editSpecificName.required = true;
                    } else {
                        editSpecificNameContainer.style.display = 'none';
                        editSpecificName.value = '';
                        editSpecificName.required = false;
                    }

                    const imageInput = document.getElementById('image');
                    const imagePreview = document.getElementById('image-preview');
                    const imageError = document.getElementById('image-error');

                    if (product.imageUrl || product.imageData) {
                        imagePreview.innerHTML = `
                            <img src="${product.imageUrl || product.imageData}" alt="${product.name}" style="max-height: 200px; max-width: 100%;">
                        `;
                    }

                    // Category change handler
                    document.getElementById('category').addEventListener('change', function() {
                        const editSpecificName = document.getElementById('editSpecificName');
                        const editSpecificNameContainer = document.getElementById('editSpecificNameContainer');
                        if (this.value === 'Other') {
                            editSpecificNameContainer.style.display = 'block';
                            editSpecificName.required = true;
                        } else {
                            editSpecificNameContainer.style.display = 'none';
                            editSpecificName.required = false;
                            editSpecificName.value = '';
                            editSpecificName.classList.remove('is-invalid');
                        }
                    });

                    imageInput.addEventListener('change', function() {
                        const file = this.files[0];
                        if (file) {
                            const fileSize = file.size / 1024 / 1024;
                            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];

                            if (fileSize > 5) {
                                imageError.textContent = 'File size exceeds 5MB limit';
                                this.value = '';
                                return;
                            }

                            if (!validTypes.includes(file.type)) {
                                imageError.textContent = 'Only JPG, JPEG, and PNG files are allowed';
                                this.value = '';
                                return;
                            }

                            imageError.textContent = '';
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                imagePreview.innerHTML = `
                                    <img src="${e.target.result}" alt="Product preview" style="max-height: 200px; max-width: 100%;">
                                `;
                            };
                            reader.readAsDataURL(file);
                        }
                    });

                    formElement.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const name = document.getElementById('name').value.trim();
                        const description = document.getElementById('description').value.trim();
                        const price = parseFloat(document.getElementById('price').value);
                        const quantity = parseInt(document.getElementById('quantity').value);
                        const unit = document.getElementById('unit').value;
                        const category = document.getElementById('category').value;
                        const organic = document.getElementById('organic').checked;
                        const specificName = document.getElementById('editSpecificName').value.trim();

                        if (!name || !price || !quantity || !unit.trim() || !category) {
                            showAlert({
                                title: 'Validation Error',
                                message: 'Please fill in all required fields',
                                type: 'warning'
                            });
                            return;
                        }

                        if (category === 'Other' && !specificName) {
                            showAlert({
                                title: 'Validation Error',
                                message: 'Please enter a specific name for the Other category',
                                type: 'warning'
                            });
                            return;
                        }

                        const submitButton = formElement.querySelector('button[type="submit"]');
                        submitButton.disabled = true;
                        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating Product...';

                        const updatedProduct = {
                            name: name,
                            description: description || '',
                            price: price,
                            quantity: quantity,
                            unit: unit.trim(),
                            category: category,
                            organic: organic,
                            updatedAt: new Date()
                        };

                        // Add specific name for Other category
                        if (category === 'Other') {
                            updatedProduct.specificName = specificName;
                        }

                        if (window.loadingOverlay) {
                            window.loadingOverlay.show('Updating product...');
                        }

                        const imageFile = document.getElementById('image').files[0];
                        let updatePromise = Promise.resolve();

                        if (imageFile) {
                            const reader = new FileReader();
                            updatePromise = new Promise((resolve, reject) => {
                                reader.onload = function(e) {
                                    const base64Image = e.target.result;
                                    if (base64Image.length > 1048487) {
                                        reject(new Error('Image too large'));
                                        return;
                                    }
                                    updatedProduct.imageData = base64Image;
                                    resolve();
                                };
                                reader.onerror = reject;
                                reader.readAsDataURL(imageFile);
                            });
                        }

                        updatePromise
                            .then(() => {
                                return firebase.firestore().collection('products').doc(productId).update(updatedProduct);
                            })
                            .then(() => {
                                window.location.href = 'my_products.php?updated=' + productId;
                            })
                            .catch(error => {
                                console.error("Error updating product: ", error);
                                errorElement.textContent = 'Error updating product. Please try again later.';
                                errorElement.style.display = 'block';
                                submitButton.disabled = false;
                                submitButton.textContent = 'Update Product';
                                if (window.loadingOverlay) {
                                    window.loadingOverlay.hide();
                                }
                            });
                    });
                })
                .catch(error => {
                    if (window.loadingOverlay) window.loadingOverlay.hide();
                    console.error("Error getting product details: ", error);

                    errorElement.textContent = 'Error loading product details. Please try again later.';
                    errorElement.style.display = 'block';
                });
        }
    </script>
</body>

</html>