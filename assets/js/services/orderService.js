class OrderService {
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

    async createOrder(cartItems, shippingInfo) {
        try {
            // Get user email from Firebase Auth
            const user = await this.getCurrentUser();

            const orderData = {
                userId: this.userId,
                buyerEmail: user.email,
                items: cartItems.map(item => ({
                    productId: item.productId || item.id,
                    productName: item.productName || item.name,
                    price: item.price,
                    quantity: item.quantity,
                    sellerId: item.sellerId,
                    sellerName: item.sellerName,
                    imageUrl: item.imageUrl,
                    imageData: item.imageData
                })),
                shippingInfo: {
                    name: shippingInfo.name,
                    phone: shippingInfo.phone,
                    address: shippingInfo.address,
                    notes: shippingInfo.notes || ''
                },
                subtotal: cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0),
                deliveryFee: 50,
                total: cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0) + 50,
                status: 'Pending',
                createdAt: firebase.firestore.Timestamp.now(),
                updatedAt: firebase.firestore.Timestamp.now()
            };

            const docRef = await this.db.collection('orders').add(orderData);
            return {
                id: docRef.id,
                ...orderData
            };
        } catch (error) {
            console.error('Error creating order:', error);
            throw error;
        }
    }

    async getUserOrders() {
        try {
            const snapshot = await this.db.collection('orders')
                .where('userId', '==', this.userId)
                .orderBy('createdAt', 'desc')
                .get();

            const orders = [];
            snapshot.forEach(doc => {
                orders.push({
                    id: doc.id,
                    ...doc.data()
                });
            });

            return orders;
        } catch (error) {
            console.error('Error getting orders:', error);
            throw error;
        }
    }

    async getOrderById(orderId) {
        try {
            const doc = await this.db.collection('orders').doc(orderId).get();
            
            if (!doc.exists) {
                throw new Error('Order not found');
            }

            return {
                id: doc.id,
                ...doc.data()
            };
        } catch (error) {
            console.error('Error getting order:', error);
            throw error;
        }
    }

    async updateOrderStatus(orderId, status) {
        try {
            await this.db.collection('orders').doc(orderId).update({
                status: status,
                updatedAt: firebase.firestore.Timestamp.now()
            });

            return true;
        } catch (error) {
            console.error('Error updating order status:', error);
            throw error;
        }
    }

    async cancelOrder(orderId, reason = '') {
        try {
            await this.db.collection('orders').doc(orderId).update({
                status: 'Cancelled',
                cancellationReason: reason,
                cancelledAt: firebase.firestore.Timestamp.now(),
                updatedAt: firebase.firestore.Timestamp.now()
            });

            return true;
        } catch (error) {
            console.error('Error cancelling order:', error);
            throw error;
        }
    }

    async getOrdersBySeller() {
        try {
            const snapshot = await this.db.collection('orders')
                .where('items.sellerId', 'array-contains', this.userId)
                .orderBy('createdAt', 'desc')
                .get();

            const orders = [];
            snapshot.forEach(doc => {
                orders.push({
                    id: doc.id,
                    ...doc.data()
                });
            });

            return orders;
        } catch (error) {
            console.error('Error getting seller orders:', error);
            throw error;
        }
    }

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

    getStatusColor(status) {
        return this.getStatusDisplayInfo(status).color;
    }

    getStatusBadgeClass(status) {
        return this.getStatusDisplayInfo(status).badge;
    }

    getStatusIcon(status) {
        return this.getStatusDisplayInfo(status).icon;
    }

    formatDate(timestamp) {
        if (!timestamp) {
            return 'Date not available';
        }

        try {
            // Handle different timestamp formats
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
                month: 'long',
                day: 'numeric'
            });
        } catch (error) {
            console.error('Error formatting date:', error);
            return 'Date error';
        }
    }

    formatDateTime(timestamp) {
        if (!timestamp) {
            return 'Date not available';
        }

        try {
            let date;
            if (timestamp.toDate) {
                date = timestamp.toDate();
            } else if (timestamp instanceof Date) {
                date = timestamp;
            } else if (typeof timestamp === 'number') {
                date = new Date(timestamp);
            } else if (typeof timestamp === 'string') {
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

    isOrderRecent(createdAt) {
        try {
            let date;
            if (createdAt && createdAt.toDate) {
                date = createdAt.toDate();
            } else if (createdAt instanceof Date) {
                date = createdAt;
            } else {
                return false;
            }

            const now = new Date();
            const diffInHours = (now - date) / (1000 * 60 * 60);
            return diffInHours < 24; // Recent if less than 24 hours old
        } catch (error) {
            console.error('Error checking if order is recent:', error);
            return false;
        }
    }
}