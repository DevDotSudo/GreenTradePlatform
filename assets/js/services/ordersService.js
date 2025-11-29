// Orders Service
// Provides order management functionality

class OrdersService {
    constructor() {
        this.collectionName = 'orders';
    }

    // Create a new order
    async createOrder(orderData) {
        try {
            const order = {
                ...orderData,
                orderDate: firebase.firestore.Timestamp.now(),
                createdAt: firebase.firestore.Timestamp.now(),
                updatedAt: firebase.firestore.Timestamp.now()
            };

            const docRef = await firebase.firestore()
                .collection(this.collectionName)
                .add(order);

            return {
                id: docRef.id,
                ...order
            };
        } catch (error) {
            console.error('Error creating order:', error);
            throw error;
        }
    }

    // Get user's orders
    async getUserOrders(userId, status = null) {
        try {
            let query = firebase.firestore()
                .collection(this.collectionName)
                .where('userId', '==', userId)
                .orderBy('createdAt', 'desc');

            if (status) {
                query = query.where('status', '==', status);
            }

            const snapshot = await query.get();
            
            if (snapshot.empty) {
                return [];
            }

            return snapshot.docs.map(doc => ({
                id: doc.id,
                ...doc.data()
            }));
        } catch (error) {
            console.error('Error getting user orders:', error);
            throw error;
        }
    }

    // Get order by ID
    async getOrderById(orderId) {
        try {
            const doc = await firebase.firestore()
                .collection(this.collectionName)
                .doc(orderId)
                .get();

            if (!doc.exists) {
                return null;
            }

            return {
                id: doc.id,
                ...doc.data()
            };
        } catch (error) {
            console.error('Error getting order by ID:', error);
            throw error;
        }
    }

    // Update order status
    async updateOrderStatus(orderId, status, notes = '') {
        try {
            const updateData = {
                status: status,
                updatedAt: firebase.firestore.Timestamp.now()
            };

            if (notes) {
                updateData.notes = notes;
            }

            // Add status history
            const order = await this.getOrderById(orderId);
            if (order && !order.statusHistory) {
                updateData.statusHistory = [];
            }

            if (order && order.statusHistory) {
                updateData.statusHistory = [
                    ...order.statusHistory,
                    {
                        status: status,
                        timestamp: firebase.firestore.Timestamp.now(),
                        notes: notes || ''
                    }
                ];
            }

            await firebase.firestore()
                .collection(this.collectionName)
                .doc(orderId)
                .update(updateData);

            return true;
        } catch (error) {
            console.error('Error updating order status:', error);
            throw error;
        }
    }

    // Get order statistics for seller
    async getOrderStats(sellerId) {
        try {
            // Get all orders that contain this seller's items
            const snapshot = await firebase.firestore()
                .collection(this.collectionName)
                .get();

            const orders = [];
            snapshot.forEach(doc => {
                const order = doc.data();
                // Check if order contains items from this seller
                if (order.items && order.items.some(item => item.sellerId === sellerId)) {
                    orders.push({
                        id: doc.id,
                        ...order
                    });
                }
            });
            
            const stats = {
                total: orders.length,
                pending: 0,
                processing: 0,
                delivered: 0,
                cancelled: 0,
                totalSpent: 0
            };

            orders.forEach(order => {
                // Count by status
                switch (order.status) {
                    case 'Pending':
                        stats.pending++;
                        break;
                    case 'Processing':
                        stats.processing++;
                        break;
                    case 'Delivered':
                        stats.delivered++;
                        // Calculate seller's portion of the total
                        const sellerItemsTotal = order.items
                            .filter(item => item.sellerId === sellerId)
                            .reduce((sum, item) => sum + ((Number(item.price) || 0) * (Number(item.quantity) || 0)), 0);
                        stats.totalSpent += sellerItemsTotal;
                        break;
                    case 'Cancelled':
                        stats.cancelled++;
                        break;
                }
            });

            return stats;
        } catch (error) {
            console.error('Error getting order stats:', error);
            throw error;
        }
    }

    // Get recent orders for seller
    async getRecentOrders(sellerId, limit = 5) {
        try {
            // Get all orders that contain this seller's items
            const snapshot = await firebase.firestore()
                .collection(this.collectionName)
                .orderBy('createdAt', 'desc')
                .get();

            if (snapshot.empty) {
                return [];
            }

            const orders = [];
            snapshot.forEach(doc => {
                const order = doc.data();
                // Check if order contains items from this seller
                if (order.items && order.items.some(item => item.sellerId === sellerId)) {
                    orders.push({
                        id: doc.id,
                        ...order
                    });
                    if (orders.length >= limit) {
                        return; // Early exit when we have enough orders
                    }
                }
            });

            return orders;
        } catch (error) {
            console.error('Error getting recent orders:', error);
            throw error;
        }
    }

    // Cancel order
    async cancelOrder(orderId, reason = '') {
        try {
            const updateData = {
                status: 'Cancelled',
                cancelledAt: firebase.firestore.Timestamp.now(),
                cancellationReason: reason,
                updatedAt: firebase.firestore.Timestamp.now()
            };

            await firebase.firestore()
                .collection(this.collectionName)
                .doc(orderId)
                .update(updateData);

            return true;
        } catch (error) {
            console.error('Error cancelling order:', error);
            throw error;
        }
    }

    // Get orders by date range
    async getOrdersByDateRange(userId, startDate, endDate) {
        try {
            const snapshot = await firebase.firestore()
                .collection(this.collectionName)
                .where('userId', '==', userId)
                .where('orderDate', '>=', startDate)
                .where('orderDate', '<=', endDate)
                .orderBy('createdAt', 'desc')
                .get();

            if (snapshot.empty) {
                return [];
            }

            return snapshot.docs.map(doc => ({
                id: doc.id,
                ...doc.data()
            }));
        } catch (error) {
            console.error('Error getting orders by date range:', error);
            throw error;
        }
    }
}

// Export for use in other files
if (typeof window !== 'undefined') {
    window.OrdersService = OrdersService;
    
    // Don't create global instance automatically - will be created after Firebase is ready
}