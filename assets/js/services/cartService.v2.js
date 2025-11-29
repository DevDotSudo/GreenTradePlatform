class CartServiceV2 {
    constructor() {
        this.db = firebase.firestore();
        this.userId = null;
    }

    async init() {
        const user = await this.getCurrentUser();
        this.userId = user.uid;
    }

    async getCurrentUser() {
        return new Promise((resolve, reject) => {
            firebase.auth().onAuthStateChanged(user => {
                if (user) {
                    resolve(user);
                } else {
                    reject(new Error('User not authenticated'));
                }
            });
        });
    }

    async getUserCart() {
        try {
            const cartDoc = await this.db.collection('carts').doc(this.userId).get();
            let cartData = {
                userId: this.userId,
                items: [],
                updatedAt: firebase.firestore.Timestamp.now()
            };

            if (cartDoc.exists) {
                cartData = cartDoc.data();
                if (!cartData.items) {
                    cartData.items = [];
                }
            }

            return cartData;
        } catch (error) {
            console.error('Error getting cart:', error);
            throw error;
        }
    }

    async addToCart(productId, quantity = 1) {
        try {
            const cartDoc = await this.getUserCart();
            const existingItemIndex = cartDoc.items.findIndex(item => item.productId === productId);

            if (existingItemIndex >= 0) {
                cartDoc.items[existingItemIndex].quantity += quantity;
            } else {
                cartDoc.items.push({
                    productId: productId,
                    quantity: quantity,
                    addedAt: firebase.firestore.Timestamp.now()
                });
            }

            await this.db.collection('carts').doc(this.userId).set({
                items: cartDoc.items,
                updatedAt: firebase.firestore.Timestamp.now()
            });

            return true;
        } catch (error) {
            console.error('Error adding to cart:', error);
            throw error;
        }
    }

    async updateCartItem(productId, quantity) {
        try {
            const cartDoc = await this.getUserCart();
            const itemIndex = cartDoc.items.findIndex(item => item.productId === productId);

            if (itemIndex >= 0) {
                if (quantity <= 0) {
                    cartDoc.items.splice(itemIndex, 1);
                } else {
                    cartDoc.items[itemIndex].quantity = quantity;
                }

                await this.db.collection('carts').doc(this.userId).set({
                    items: cartDoc.items,
                    updatedAt: firebase.firestore.Timestamp.now()
                });

                return true;
            }

            return false;
        } catch (error) {
            console.error('Error updating cart item:', error);
            throw error;
        }
    }

    async removeFromCart(productId) {
        try {
            const cartDoc = await this.getUserCart();
            const updatedItems = cartDoc.items.filter(item => item.productId !== productId);

            await this.db.collection('carts').doc(this.userId).set({
                items: updatedItems,
                updatedAt: firebase.firestore.Timestamp.now()
            });

            return true;
        } catch (error) {
            console.error('Error removing from cart:', error);
            throw error;
        }
    }

    async clearCart() {
        try {
            await this.db.collection('carts').doc(this.userId).set({
                items: [],
                updatedAt: firebase.firestore.Timestamp.now()
            });

            return true;
        } catch (error) {
            console.error('Error clearing cart:', error);
            throw error;
        }
    }

    async validateCartItems() {
        try {
            const cart = await this.getUserCart();
            const validation = {
                total: cart.items.length,
                valid: 0,
                invalid: 0,
                invalidItems: [],
                isValid: true
            };

            for (const item of cart.items) {
                try {
                    const productDoc = await this.db.collection('products').doc(item.productId).get();
                    
                    if (!productDoc.exists) {
                        validation.invalidItems.push({
                            productId: item.productId,
                            productName: 'Unknown Product',
                            reason: 'Product no longer exists',
                            originalReason: 'Product not found'
                        });
                        validation.invalid++;
                    } else {
                        const productData = productDoc.data();
                        if (!productData) {
                            validation.invalidItems.push({
                                productId: item.productId,
                                productName: 'Unknown Product',
                                reason: 'Invalid product data',
                                originalReason: 'Product data corrupted'
                            });
                            validation.invalid++;
                        } else {
                            const productQuantity = Number(productData.quantity) || 0;
                            const requestedQuantity = Number(item.quantity) || 0;
                            
                            if (productQuantity < requestedQuantity) {
                                validation.invalidItems.push({
                                    productId: item.productId,
                                    productName: productData.name || 'Unknown Product',
                                    reason: `Only ${productQuantity} items available`,
                                    originalReason: 'Insufficient stock'
                                });
                                validation.invalid++;
                            } else {
                                validation.valid++;
                            }
                        }
                    }
                } catch (itemError) {
                    console.error('Error validating item:', itemError);
                    validation.invalidItems.push({
                        productId: item.productId,
                        productName: 'Unknown Product',
                        reason: 'Error validating product',
                        originalReason: 'Validation error'
                    });
                    validation.invalid++;
                }
            }

            validation.isValid = validation.invalid === 0;
            return validation;
        } catch (error) {
            console.error('Error validating cart:', error);
            throw error;
        }
    }

    async getCartTotal() {
        try {
            const cart = await this.getUserCart();
            let total = 0;

            for (const item of cart.items) {
                try {
                    const productDoc = await this.db.collection('products').doc(item.productId).get();
                    if (productDoc.exists) {
                        const productData = productDoc.data();
                        const price = Number(productData.price) || 0;
                        const quantity = Number(item.quantity) || 0;
                        total += price * quantity;
                    }
                } catch (itemError) {
                    console.error('Error getting product for total:', itemError);
                }
            }

            return total;
        } catch (error) {
            console.error('Error getting cart total:', error);
            throw error;
        }
    }
}