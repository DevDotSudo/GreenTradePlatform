function getProducts(options = {}) {
    const defaultOptions = {
        category: null,
        sellerId: null,
        limit: 50,
        sortBy: 'createdAt',
        sortDirection: 'desc'
    };
    const finalOptions = { ...defaultOptions, ...options };

    return waitForFirebase(() => {
        let query = firebase.firestore().collection('products');

        if (finalOptions.category) {
            query = query.where('category', '==', finalOptions.category);
        }
        if (finalOptions.sellerId) {
            query = query.where('sellerId', '==', finalOptions.sellerId);
        }
        query = query.orderBy(finalOptions.sortBy, finalOptions.sortDirection);
        if (finalOptions.limit) {
            query = query.limit(finalOptions.limit);
        }

        return query.get().then(snapshot => {
            const products = [];
            snapshot.forEach(doc => {
                products.push({ id: doc.id, ...doc.data() });
            });
            return products;
        });
    });
}

function getProduct(productId) {
    return waitForFirebase(() => {
        return firebase.firestore().collection('products').doc(productId).get()
            .then(doc => {
                if (doc.exists) {
                    return { id: doc.id, ...doc.data() };
                } else {
                    throw new Error('Product not found');
                }
            });
    });
}

function addProduct(productData) {
    return waitForFirebase(() => {
        return firebase.firestore().collection('products').add({
            ...productData,
            createdAt: firebase.firestore.FieldValue.serverTimestamp(),
            updatedAt: firebase.firestore.FieldValue.serverTimestamp()
        });
    });
}

function updateProduct(productId, productData) {
    return waitForFirebase(() => {
        return firebase.firestore().collection('products').doc(productId).update({
            ...productData,
            updatedAt: firebase.firestore.FieldValue.serverTimestamp()
        });
    });
}

function deleteProduct(productId) {
    return waitForFirebase(() => {
        return firebase.firestore().collection('products').doc(productId).delete();
    });
}

function searchProducts(searchTerm) {
    return getProducts({ limit: 100 }).then(products => {
        const lowerSearchTerm = searchTerm.toLowerCase();
        return products.filter(product =>
            product.name.toLowerCase().includes(lowerSearchTerm) ||
            (product.description && product.description.toLowerCase().includes(lowerSearchTerm))
        );
    });
}

function getProductsByCategory(category) {
    return getProducts({ category });
}

function getSellerProducts(sellerId) {
    return getProducts({ sellerId });
}

function uploadProductImage(productId, file) {
    return waitForFirebase(() => {
        const storageRef = firebase.storage().ref();
        const fileRef = storageRef.child(`products/${productId}/${file.name}`);

        return fileRef.put(file)
            .then(snapshot => snapshot.ref.getDownloadURL())
            .then(downloadURL => {
                return updateProduct(productId, { imageUrl: downloadURL })
                    .then(() => downloadURL);
            });
    });
}

function formatProductPrice(price) {
    return '₱' + parseFloat(price).toFixed(2);
}
