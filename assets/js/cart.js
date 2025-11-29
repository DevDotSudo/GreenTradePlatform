document.addEventListener('DOMContentLoaded', () => {
    console.log('Cart.js loaded');
    initializeCart();
});

function initializeCart() {
    waitForFirebase(() => {
        firebase.auth().onAuthStateChanged(user => {
            if (user) {
                window.currentUserId = user.uid;
                console.log('Cart user ID set:', user.uid);
                updateCartBadge();
            } else {
                console.log('No user logged in for cart operations');
            }
        });
    });
}

window.addToCart = async function(product, quantity = 1) {
    try {
        await new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Firebase auth timeout')), 10000);
            
            firebase.auth().onAuthStateChanged(user => {
                clearTimeout(timeout);
                if (user) {
                    resolve(user.uid);
                } else {
                    reject(new Error('User not logged in'));
                }
            }, { onlyOnce: true });
        }).then(userId => {
            return addToCartInternal(userId, product, quantity);
        });
        
        return true;
    } catch (error) {
        console.error('Error adding to cart:', error);
        throw error;
    }
};

async function addToCartInternal(userId, product, quantity) {
    const cartRef = firebase.firestore().collection('carts').doc(userId);
    const cartDoc = await cartRef.get();

    let cartData = {
        userId: userId,
        items: [],
        updatedAt: firebase.firestore.Timestamp.now()
    };

    if (cartDoc.exists) {
        cartData = cartDoc.data();
        cartData.updatedAt = firebase.firestore.Timestamp.now();
    }

    if (!cartData.items) {
        cartData.items = [];
    }

    const existingItemIndex = cartData.items.findIndex(item =>
        (item.productId === product.id) || (item.id === product.id)
    );

    if (existingItemIndex >= 0) {
        cartData.items[existingItemIndex].quantity += quantity;
        // Ensure all fields are present
        cartData.items[existingItemIndex].productId = product.id;
        cartData.items[existingItemIndex].productName = product.name;
        cartData.items[existingItemIndex].name = product.name;
        cartData.items[existingItemIndex].price = product.price;
        cartData.items[existingItemIndex].sellerId = product.sellerId || '';
        cartData.items[existingItemIndex].sellerName = product.sellerName || '';
        cartData.items[existingItemIndex].imageUrl = product.imageUrl || null;
        cartData.items[existingItemIndex].imageData = product.imageData || null;
    } else {
        cartData.items.push({
            productId: product.id,
            id: product.id,
            productName: product.name,
            name: product.name,
            price: product.price,
            quantity: quantity,
            sellerId: product.sellerId || '',
            sellerName: product.sellerName || '',
            imageUrl: product.imageUrl || null,
            imageData: product.imageData || null,
            addedAt: firebase.firestore.Timestamp.now()
        });
    }

    await cartRef.set(cartData);
    console.log('Product added to cart:', product.name);

    if (typeof showToast === 'function') {
        showToast({
            title: 'Added to Cart',
            message: `${product.name} has been added to your cart.`,
            type: 'success'
        });
    } else if (typeof showAlert === 'function') {
        showAlert({
            title: 'Added to Cart',
            message: `${product.name} has been added to your cart.`,
            type: 'success'
        });
    } else {
        alert(`${product.name} has been added to your cart.`);
    }

    return true;
}

window.updateCartBadge = function() {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;

    firebase.auth().onAuthStateChanged(user => {
        if (!user) {
            badge.textContent = '0';
            return;
        }

        firebase.firestore().collection('carts')
            .doc(user.uid)
            .get()
            .then(doc => {
                let totalItems = 0;
                if (doc.exists) {
                    const cart = doc.data();
                    const items = Array.isArray(cart.items) ? cart.items : [];
                    totalItems = items.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
                }
                badge.textContent = totalItems;
            })
            .catch(error => {
                console.error("Error updating cart badge: ", error);
                badge.textContent = '0';
            });
    });
};