// Order Manager
// Enhanced order management functionality

class OrderManager {
    constructor() {
        this._ordersService = null;
        this._cartService = null;
    }

    get ordersService() {
        if (!this._ordersService) {
            this._ordersService = window.ordersService || new OrdersService();
        }
        return this._ordersService;
    }

    get cartService() {
        if (!this._cartService) {
            this._cartService = window.cartService || new CartService();
        }
        return this._cartService;
    }

    // Create order from cart
    async createOrderFromCart(userId, shippingInfo) {
        try {
            // Get user's cart
            const cart = await this.cartService.getUserCart(userId);
            
            if (!cart || !cart.items || cart.items.length === 0) {
                throw new Error('Cart is empty');
            }

            // Calculate order totals
            let subtotal = 0;
            const orderItems = [];

            for (const item of cart.items) {
                try {
                    // Get product details
                    const productDoc = await firebase.firestore()
                        .collection('products')
                        .doc(item.productId)
                        .get();

                    if (!productDoc.exists) {
                        throw new Error(`Product not found: ${item.productId}`);
                    }

                    const product = productDoc.data();
                    const itemTotal = product.price * item.quantity;
                    subtotal += itemTotal;

                    orderItems.push({
                        productId: item.productId,
                        productName: product.name,
                        productPrice: product.price,
                        quantity: item.quantity,
                        sellerId: product.sellerId,
                        sellerName: product.sellerName,
                        total: itemTotal
                    });
                } catch (error) {
                    console.error('Error processing cart item:', error);
                    throw new Error(`Failed to process product: ${item.productId}`);
                }
            }

            // Calculate shipping (simplified - could be more complex)
            const shippingCost = this.calculateShipping(orderItems);
            const taxAmount = this.calculateTax(subtotal);
            const totalAmount = subtotal + shippingCost + taxAmount;

            // Create order
            const orderData = {
                buyerId: userId,
                items: orderItems,
                subtotal: subtotal,
                shippingCost: shippingCost,
                taxAmount: taxAmount,
                totalAmount: totalAmount,
                status: 'Pending',
                shippingInfo: shippingInfo,
                paymentMethod: 'Cash on Delivery', // Default
                paymentStatus: 'Pending'
            };

            const order = await this.ordersService.createOrder(orderData);

            // Clear the cart after successful order
            await this.cartService.clearCart(userId);

            return order;
        } catch (error) {
            console.error('Error creating order from cart:', error);
            throw error;
        }
    }

    // Calculate shipping cost (simplified)
    calculateShipping(items) {
        // Base shipping cost logic - can be enhanced
        const baseShipping = 50; // ₱50 base
        const itemCount = items.length;
        
        if (itemCount > 5) {
            return baseShipping + 20; // Additional for bulk orders
        }
        
        return baseShipping;
    }

    // Calculate tax (simplified)
    calculateTax(subtotal) {
        // 12% VAT
        return subtotal * 0.12;
    }

    // Update order status with notification
    async updateOrderStatus(orderId, status, notes = '') {
        try {
            await this.ordersService.updateOrderStatus(orderId, status, notes);
            
            // Send notification (if notifications system exists)
            this.sendOrderNotification(orderId, status, notes);
            
            return true;
        } catch (error) {
            console.error('Error updating order status:', error);
            throw error;
        }
    }

    // Send order notification
    sendOrderNotification(orderId, status, notes) {
        try {
            // This would integrate with your notification system
            if (window.showToast) {
                window.showToast({
                    title: 'Order Update',
                    message: `Order #${orderId.substring(0, 8)} status updated to: ${status}`,
                    type: 'info'
                });
            }
        } catch (error) {
            console.error('Error sending order notification:', error);
        }
    }

    // Get order with full details
    async getOrderDetails(orderId) {
        try {
            const order = await this.ordersService.getOrderById(orderId);
            
            if (!order) {
                return null;
            }

            // Enhance with product details
            if (order.items) {
                for (let i = 0; i < order.items.length; i++) {
                    try {
                        const productDoc = await firebase.firestore()
                            .collection('products')
                            .doc(order.items[i].productId)
                            .get();

                        if (productDoc.exists) {
                            order.items[i].productDetails = productDoc.data();
                        }
                    } catch (error) {
                        console.error('Error fetching product details:', error);
                    }
                }
            }

            return order;
        } catch (error) {
            console.error('Error getting order details:', error);
            throw error;
        }
    }

    // Validate order before submission
    validateOrder(cart, shippingInfo) {
        const errors = [];

        if (!cart || !cart.items || cart.items.length === 0) {
            errors.push('Cart is empty');
        }

        if (!shippingInfo) {
            errors.push('Shipping information is required');
        } else {
            if (!shippingInfo.address || shippingInfo.address.trim().length < 10) {
                errors.push('Valid shipping address is required');
            }
            if (!shippingInfo.phone || shippingInfo.phone.trim().length < 10) {
                errors.push('Valid phone number is required');
            }
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    }

    // Format order for display
    formatOrderForDisplay(order) {
        if (!order) return null;

        return {
            id: order.id,
            orderNumber: order.id.substring(0, 8),
            status: order.status,
            orderDate: order.orderDate?.toDate ? order.orderDate.toDate() : new Date(order.orderDate),
            totalAmount: order.totalAmount,
            itemCount: order.items?.length || 0,
            shippingInfo: order.shippingInfo,
            paymentStatus: order.paymentStatus || 'Pending',
            statusHistory: order.statusHistory || []
        };
    }

    // Add methods that dashboard expects directly to the instance
    async getOrderStats(userId) {
        return this.ordersService.getOrderStats(userId);
    }

    async getUserOrders(userId) {
        return this.ordersService.getUserOrders(userId);
    }

    async getRecentOrders(userId, limit = 5) {
        return this.ordersService.getRecentOrders(userId, limit);
    }

    // Format date time helper
    formatDateTime(timestamp) {
        if (!timestamp) {
            return 'Date not available';
        }

        try {
            let date;
            if (timestamp.toDate) {
                // Firebase Timestamp object
                date = timestamp.toDate();
            } else if (timestamp instanceof Date) {
                // JavaScript Date object
                date = timestamp;
            } else if (typeof timestamp === 'number') {
                // Unix timestamp
                date = new Date(timestamp);
            } else if (typeof timestamp === 'string') {
                // ISO string
                date = new Date(timestamp);
            } else {
                return 'Invalid date';
            }

            return date.toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.error('Error formatting date time:', error);
            return 'Date error';
        }
    }

    // Get status display info
    getStatusDisplayInfo(status) {
        const statusMap = {
            'Pending': {
                label: 'Pending',
                badge: 'bg-warning',
                color: '#ffc107',
                icon: 'clock',
                description: 'Order received and waiting for processing'
            },
            'Processing': {
                label: 'Processing',
                badge: 'bg-info',
                color: '#17a2b8',
                icon: 'package',
                description: 'Order is being prepared by seller'
            },
            'Out for Delivery': {
                label: 'Out for Delivery',
                badge: 'bg-primary',
                color: '#007bff',
                icon: 'truck',
                description: 'Order is on the way to delivery'
            },
            'Delivered': {
                label: 'Delivered',
                badge: 'bg-success',
                color: '#28a745',
                icon: 'check-circle',
                description: 'Order has been successfully delivered'
            },
            'Cancelled': {
                label: 'Cancelled',
                badge: 'bg-danger',
                color: '#dc3545',
                icon: 'x-circle',
                description: 'Order has been cancelled'
            }
        };

        return statusMap[status] || {
            label: status,
            badge: 'bg-secondary',
            color: '#6c757d',
            icon: 'help-circle',
            description: 'Unknown status'
        };
    }
}

// Export for use in other files
if (typeof window !== 'undefined') {
    window.OrderManager = OrderManager;
    
    // Don't create global instance automatically - will be created after Firebase is ready
}

// Service methods for backward compatibility
window.getOrderStats = function(userId) {
    if (window.orderManager && window.orderManager.ordersService) {
        return window.orderManager.ordersService.getOrderStats(userId);
    }
    return Promise.reject(new Error('Order service not available'));
};

window.getUserOrders = function(userId) {
    if (window.orderManager && window.orderManager.ordersService) {
        return window.orderManager.ordersService.getUserOrders(userId);
    }
    return Promise.reject(new Error('Order service not available'));
};