function addToCart(product, quantity = 1) {
    return new Promise((resolve, reject) => {
        waitForFirebase(() => {
            checkAuth()
                .then(user => {
                    const userId = user.uid;

                    return firebase.firestore().collection('carts')
                        .where('userId', '==', userId)
                        .limit(1)
                        .get()
                        .then(snapshot => {
                            if (snapshot.empty) {
                                return firebase.firestore().collection('carts').add({
                                    userId,
                                    items: [{
                                        productId: product.id,
                                        name: product.name,
                                        price: product.price,
                                        quantity,
                                        sellerId: product.sellerId,
                                        sellerName: product.sellerName,
                                        imageUrl: product.imageUrl || null
                                    }],
                                    createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                                    updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                                });
                            } else {
                                const cartDoc = snapshot.docs[0];
                                const cart = cartDoc.data();
                                const existingItemIndex = cart.items.findIndex(item => item.productId === product.id);

                                if (existingItemIndex !== -1) {
                                    cart.items[existingItemIndex].quantity += quantity;
                                } else {
                                    cart.items.push({
                                        productId: product.id,
                                        name: product.name,
                                        price: product.price,
                                        quantity,
                                        sellerId: product.sellerId,
                                        sellerName: product.sellerName,
                                        imageUrl: product.imageUrl || null
                                    });
                                }

                                return firebase.firestore().collection('carts').doc(cartDoc.id).update({
                                    items: cart.items,
                                    updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                                });
                            }
                        })
                        .then(() => {
                            updateCartBadge();
                            showToast({
                                title: 'Added to Cart',
                                message: `${quantity} ${quantity === 1 ? 'item' : 'items'} of ${product.name} added to cart!`,
                                type: 'success'
                            });
                            resolve(`${product.name} added to cart!`);
                        });
                })
                .catch(error => {
                    console.error("Error adding to cart:", error);
                    if (error.message === "User not authenticated") {
                        showToast({
                            title: 'Authentication Required',
                            message: 'Please login to add items to cart.',
                            type: 'warning'
                        });
                        setTimeout(() => {
                            window.location.href = "/login.php";
                        }, 2000);
                        reject("Please login to add items to cart");
                    } else {
                        showToast({
                            title: 'Error',
                            message: 'Error adding to cart. Please try again later.',
                            type: 'error'
                        });
                        reject("Error adding to cart. Please try again later.");
                    }
                });
        });
    });
}

function removeFromCart(cartId, productId, productName = 'Item') {
    return new Promise((resolve, reject) => {
        showConfirm({
            title: 'Remove Item',
            message: `Are you sure you want to remove "${productName}" from your cart?`,
            type: 'warning',
            confirmText: 'Remove',
            cancelText: 'Cancel',
            onConfirm: () => {
                waitForFirebase(() => {
                    firebase.firestore().collection('carts').doc(cartId).get()
                        .then(doc => {
                            if (!doc.exists) throw new Error("Cart not found");
                            const updatedItems = doc.data().items.filter(item => item.productId !== productId);
                            return firebase.firestore().collection('carts').doc(cartId).update({
                                items: updatedItems,
                                updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                            });
                        })
                        .then(() => {
                            updateCartBadge();
                            showToast({
                                title: 'Item Removed',
                                message: `${productName} has been removed from your cart.`,
                                type: 'success'
                            });
                            resolve("Item removed from cart");
                        })
                        .catch(err => {
                            console.error("Error removing from cart:", err);
                            showToast({
                                title: 'Error',
                                message: 'Error removing item from cart. Please try again later.',
                                type: 'error'
                            });
                            reject("Error removing item from cart. Please try again later.");
                        });
                });
            },
            onCancel: () => {
                reject("Operation cancelled");
            }
        });
    });
}

function updateCartItemQuantity(cartId, productId, newQuantity, productName = 'Item') {
    if (newQuantity < 1) {
        return removeFromCart(cartId, productId, productName);
    }

    return new Promise((resolve, reject) => {
        waitForFirebase(() => {
            firebase.firestore().collection('carts').doc(cartId).get()
                .then(doc => {
                    if (!doc.exists) throw new Error("Cart not found");
                    const cart = doc.data();
                    const itemIndex = cart.items.findIndex(item => item.productId === productId);
                    if (itemIndex === -1) throw new Error("Item not found in cart");
                    
                    cart.items[itemIndex].quantity = newQuantity;
                    return firebase.firestore().collection('carts').doc(cartId).update({
                        items: cart.items,
                        updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                    });
                })
                .then(() => {
                    updateCartBadge();
                    showToast({
                        title: 'Quantity Updated',
                        message: `Quantity of ${productName} updated to ${newQuantity}.`,
                        type: 'success'
                    });
                    resolve("Quantity updated");
                })
                .catch(err => {
                    console.error("Error updating cart item quantity:", err);
                    showToast({
                        title: 'Error',
                        message: 'Error updating quantity. Please try again later.',
                        type: 'error'
                    });
                    reject("Error updating quantity. Please try again later.");
                });
        });
    });
}

function clearCart(cartId) {
    return new Promise((resolve, reject) => {
        showConfirm({
            title: 'Clear Cart',
            message: 'Are you sure you want to remove all items from your cart? This action cannot be undone.',
            type: 'warning',
            confirmText: 'Clear Cart',
            cancelText: 'Cancel',
            onConfirm: () => {
                waitForFirebase(() => {
                    firebase.firestore().collection('carts').doc(cartId).update({
                        items: [],
                        updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                    })
                    .then(() => {
                        updateCartBadge();
                        showToast({
                            title: 'Cart Cleared',
                            message: 'All items have been removed from your cart.',
                            type: 'success'
                        });
                        resolve("Cart cleared");
                    })
                    .catch(err => {
                        console.error("Error clearing cart:", err);
                        showToast({
                            title: 'Error',
                            message: 'Error clearing cart. Please try again later.',
                            type: 'error'
                        });
                        reject("Error clearing cart. Please try again later.");
                    });
                });
            },
            onCancel: () => {
                reject("Operation cancelled");
            }
        });
    });
}

function getUserCart() {
    return new Promise((resolve, reject) => {
        waitForFirebase(() => {
            checkAuth()
                .then(user => {
                    return firebase.firestore().collection('carts')
                        .where('userId', '==', user.uid)
                        .limit(1)
                        .get()
                        .then(snapshot => {
                            if (snapshot.empty) {
                                resolve(null);
                            } else {
                                const doc = snapshot.docs[0];
                                resolve({ id: doc.id, ...doc.data() });
                            }
                        });
                })
                .catch(err => {
                    console.error("Error getting user cart:", err);
                    if (err.message === "User not authenticated") {
                        showToast({
                            title: 'Authentication Required',
                            message: 'Please login to view your cart.',
                            type: 'warning'
                        });
                        setTimeout(() => {
                            window.location.href = "/login.php";
                        }, 2000);
                    }
                    reject(err);
                });
        });
    });
}

function updateCartBadge() {
    const badge = document.getElementById('cart-badge');
    if (!badge) return Promise.resolve();

    return new Promise((resolve, reject) => {
        waitForFirebase(() => {
            checkAuth()
                .then(user => {
                    return firebase.firestore().collection('carts')
                        .where('userId', '==', user.uid)
                        .limit(1)
                        .get()
                        .then(snapshot => {
                            if (snapshot.empty) {
                                badge.textContent = '0';
                            } else {
                                const cart = snapshot.docs[0].data();
                                const totalItems = (cart.items || []).reduce((sum, item) => sum + item.quantity, 0);
                                badge.textContent = totalItems.toString();
                            }
                            resolve();
                        });
                })
                .catch(err => {
                    if (err.message !== "User not authenticated") {
                        console.error("Error updating cart badge:", err);
                    }
                    badge.textContent = '0';
                    resolve(); // Still resolve even on error for badge updates
                });
        });
    });
}

// Enhanced cart functions with better UX
function checkoutCart(cartId, cartItems) {
    return new Promise((resolve, reject) => {
        if (!cartItems || cartItems.length === 0) {
            showToast({
                title: 'Empty Cart',
                message: 'Your cart is empty. Please add some items before checkout.',
                type: 'warning'
            });
            reject("Cart is empty");
            return;
        }

        const totalAmount = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        showConfirm({
            title: 'Confirm Checkout',
            message: `Total amount: ₱${totalAmount.toFixed(2)}\n\nAre you sure you want to proceed with checkout?`,
            type: 'info',
            confirmText: 'Proceed to Checkout',
            cancelText: 'Review Cart',
            onConfirm: () => {
                // Redirect to checkout page
                window.location.href = '/buyer/checkout.php';
                resolve("Proceeding to checkout");
            },
            onCancel: () => {
                reject("Checkout cancelled");
            }
        });
    });
}

// Initialize cart badge on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();
});