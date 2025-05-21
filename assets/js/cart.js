/**
 * Shopping Cart Functions
 * This script handles cart-related functionalities
 */

// Add an item to the cart
function addToCart(product, quantity) {
    // Get the current user
    return checkAuth()
        .then(user => {
            const userId = user.uid;
            
            // Check if user already has a cart
            return firebase.firestore().collection('carts')
                .where('userId', '==', userId)
                .limit(1)
                .get()
                .then(snapshot => {
                    if (snapshot.empty) {
                        // Create a new cart for the user
                        return firebase.firestore().collection('carts').add({
                            userId: userId,
                            items: [
                                {
                                    productId: product.id,
                                    name: product.name,
                                    price: product.price,
                                    quantity: quantity,
                                    sellerId: product.sellerId,
                                    sellerName: product.sellerName
                                }
                            ],
                            createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                            updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                        });
                    } else {
                        // Update existing cart
                        const cartDoc = snapshot.docs[0];
                        const cart = cartDoc.data();
                        
                        // Check if product already exists in cart
                        const existingItemIndex = cart.items.findIndex(item => item.productId === product.id);
                        
                        if (existingItemIndex !== -1) {
                            // Update quantity of existing item
                            cart.items[existingItemIndex].quantity += quantity;
                        } else {
                            // Add new item to cart
                            cart.items.push({
                                productId: product.id,
                                name: product.name,
                                price: product.price,
                                quantity: quantity,
                                sellerId: product.sellerId,
                                sellerName: product.sellerName
                            });
                        }
                        
                        // Update cart in Firestore
                        return firebase.firestore().collection('carts').doc(cartDoc.id).update({
                            items: cart.items,
                            updatedAt: firebase.firestore.FieldValue.serverTimestamp()
                        });
                    }
                })
                .then(() => {
                    // Show success message
                    alert(`${product.name} added to cart!`);
                    
                    // Update cart badge if it exists
                    updateCartBadge();
                });
        })
        .catch(error => {
            console.error("Error adding to cart: ", error);
            
            if (error.message === "User not authenticated") {
                // Redirect to login page
                window.location.href = "/login.php";
            } else {
                alert("Error adding to cart. Please try again later.");
            }
        });
}

// Remove an item from the cart
function removeFromCart(cartId, productId) {
    return firebase.firestore().collection('carts').doc(cartId).get()
        .then(doc => {
            if (!doc.exists) {
                throw new Error("Cart not found");
            }
            
            const cart = doc.data();
            
            // Filter out the item to remove
            const updatedItems = cart.items.filter(item => item.productId !== productId);
            
            // Update cart in Firestore
            return firebase.firestore().collection('carts').doc(cartId).update({
                items: updatedItems,
                updatedAt: firebase.firestore.FieldValue.serverTimestamp()
            });
        })
        .then(() => {
            // Show success message
            // alert("Item removed from cart");
            
            // Update cart badge
            updateCartBadge();
        })
        .catch(error => {
            console.error("Error removing from cart: ", error);
            alert("Error removing item from cart. Please try again later.");
        });
}

// Update cart item quantity
function updateCartItemQuantity(cartId, productId, newQuantity) {
    return firebase.firestore().collection('carts').doc(cartId).get()
        .then(doc => {
            if (!doc.exists) {
                throw new Error("Cart not found");
            }
            
            const cart = doc.data();
            
            // Find the item to update
            const itemIndex = cart.items.findIndex(item => item.productId === productId);
            
            if (itemIndex === -1) {
                throw new Error("Item not found in cart");
            }
            
            // Update the quantity
            cart.items[itemIndex].quantity = newQuantity;
            
            // Update cart in Firestore
            return firebase.firestore().collection('carts').doc(cartId).update({
                items: cart.items,
                updatedAt: firebase.firestore.FieldValue.serverTimestamp()
            });
        })
        .then(() => {
            // Update cart badge
            updateCartBadge();
        })
        .catch(error => {
            console.error("Error updating cart item quantity: ", error);
            alert("Error updating quantity. Please try again later.");
        });
}

// Clear the entire cart
function clearCart(cartId) {
    return firebase.firestore().collection('carts').doc(cartId).update({
        items: [],
        updatedAt: firebase.firestore.FieldValue.serverTimestamp()
    })
    .then(() => {
        // Update cart badge
        updateCartBadge();
    })
    .catch(error => {
        console.error("Error clearing cart: ", error);
        alert("Error clearing cart. Please try again later.");
    });
}

// Get the user's cart
function getUserCart() {
    return checkAuth()
        .then(user => {
            const userId = user.uid;
            
            return firebase.firestore().collection('carts')
                .where('userId', '==', userId)
                .limit(1)
                .get()
                .then(snapshot => {
                    if (snapshot.empty) {
                        // User doesn't have a cart yet
                        return null;
                    }
                    
                    const cartDoc = snapshot.docs[0];
                    return {
                        id: cartDoc.id,
                        ...cartDoc.data()
                    };
                });
        })
        .catch(error => {
            console.error("Error getting user cart: ", error);
            
            if (error.message === "User not authenticated") {
                // Redirect to login page
                window.location.href = "/login.php";
            } else {
                throw error;
            }
        });
}

// Update the cart badge with current item count
function updateCartBadge() {
    const cartBadge = document.getElementById('cart-badge');
    
    if (cartBadge) {
        checkAuth()
            .then(user => {
                const userId = user.uid;
                
                return firebase.firestore().collection('carts')
                    .where('userId', '==', userId)
                    .limit(1)
                    .get()
                    .then(snapshot => {
                        if (snapshot.empty) {
                            cartBadge.textContent = '0';
                            return;
                        }
                        
                        const cartDoc = snapshot.docs[0];
                        const cart = cartDoc.data();
                        
                        // Count total items in cart
                        let totalItems = 0;
                        if (cart.items) {
                            cart.items.forEach(item => {
                                totalItems += item.quantity;
                            });
                        }
                        
                        cartBadge.textContent = totalItems.toString();
                    });
            })
            .catch(error => {
                // Ignore auth errors, just don't update the badge
                if (error.message !== "User not authenticated") {
                    console.error("Error updating cart badge: ", error);
                }
            });
    }
}

// Calculate cart subtotal
function calculateCartSubtotal(cart) {
    if (!cart || !cart.items || cart.items.length === 0) {
        return 0;
    }
    
    return cart.items.reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0);
}
