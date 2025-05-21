/**
 * Product Management Functions
 * This script handles product-related functionalities
 */

// Get products from Firestore
function getProducts(options = {}) {
    // Default options
    const defaultOptions = {
        category: null,
        sellerId: null,
        limit: 50,
        sortBy: 'createdAt',
        sortDirection: 'desc'
    };
    
    // Merge options with defaults
    const finalOptions = {...defaultOptions, ...options};
    
    // Start with base query
    let query = firebase.firestore().collection('products');
    
    // Apply category filter if provided
    if (finalOptions.category) {
        query = query.where('category', '==', finalOptions.category);
    }
    
    // Apply seller filter if provided
    if (finalOptions.sellerId) {
        query = query.where('sellerId', '==', finalOptions.sellerId);
    }
    
    // Apply sorting
    query = query.orderBy(finalOptions.sortBy, finalOptions.sortDirection);
    
    // Apply limit if provided
    if (finalOptions.limit) {
        query = query.limit(finalOptions.limit);
    }
    
    // Execute query
    return query.get()
        .then(snapshot => {
            const products = [];
            snapshot.forEach(doc => {
                products.push({
                    id: doc.id,
                    ...doc.data()
                });
            });
            return products;
        });
}

// Get a single product by ID
function getProduct(productId) {
    return firebase.firestore().collection('products').doc(productId).get()
        .then(doc => {
            if (doc.exists) {
                return {
                    id: doc.id,
                    ...doc.data()
                };
            } else {
                throw new Error('Product not found');
            }
        });
}

// Add a new product
function addProduct(productData) {
    return firebase.firestore().collection('products').add({
        ...productData,
        createdAt: firebase.firestore.FieldValue.serverTimestamp(),
        updatedAt: firebase.firestore.FieldValue.serverTimestamp()
    });
}

// Update an existing product
function updateProduct(productId, productData) {
    return firebase.firestore().collection('products').doc(productId).update({
        ...productData,
        updatedAt: firebase.firestore.FieldValue.serverTimestamp()
    });
}

// Delete a product
function deleteProduct(productId) {
    return firebase.firestore().collection('products').doc(productId).delete();
}

// Search products by name or description
function searchProducts(searchTerm) {
    // Firebase doesn't support full text search natively,
    // so we'll perform a client-side search
    return getProducts({ limit: 100 })
        .then(products => {
            const lowerSearchTerm = searchTerm.toLowerCase();
            return products.filter(product => 
                product.name.toLowerCase().includes(lowerSearchTerm) ||
                (product.description && product.description.toLowerCase().includes(lowerSearchTerm))
            );
        });
}

// Get products by category
function getProductsByCategory(category) {
    return getProducts({ category });
}

// Get seller's products
function getSellerProducts(sellerId) {
    return getProducts({ sellerId });
}

// Upload product image to Firebase Storage
function uploadProductImage(productId, file) {
    // Create a storage reference
    const storageRef = firebase.storage().ref();
    const fileRef = storageRef.child(`products/${productId}/${file.name}`);
    
    // Upload the file
    return fileRef.put(file)
        .then(snapshot => snapshot.ref.getDownloadURL())
        .then(downloadURL => {
            // Update the product with the image URL
            return updateProduct(productId, { imageUrl: downloadURL })
                .then(() => downloadURL);
        });
}

// Format product price
function formatProductPrice(price) {
    return '₱' + parseFloat(price).toFixed(2);
}
