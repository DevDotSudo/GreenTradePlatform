<?php
require_once __DIR__ . '/includes/session.php';
include 'includes/auth.php';
include 'includes/functions.php';

ensureUserLoggedIn('buyer');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Debug - Green Trade</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Orders Debug</h1>
                <p class="page-subtitle">Debugging order loading issues</p>
            </div>

            <div id="debug-results" class="debug-container">
                <div class="loading-state">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Running diagnostics...</div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>

    <!-- Core Dependencies -->
    <script src="assets/js/firebase.js"></script>
    <script src="assets/js/dialogs.js"></script>

    <!-- Services -->
    <script src="assets/js/services/orderService.js"></script>

    <style>
        .debug-container {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            padding: var(--space-6);
            margin-bottom: var(--space-6);
        }

        .debug-section {
            margin-bottom: var(--space-4);
            padding: var(--space-4);
            border-left: 4px solid var(--primary-500);
            background: var(--gray-50);
        }

        .debug-success {
            border-left-color: var(--success-500);
            background: var(--success-50);
        }

        .debug-error {
            border-left-color: var(--error-500);
            background: var(--error-50);
        }

        .debug-info {
            border-left-color: var(--info-500);
            background: var(--info-50);
        }

        pre {
            background: #f8f9fa;
            padding: var(--space-3);
            border-radius: var(--radius-md);
            overflow-x: auto;
            font-size: 0.875rem;
            white-space: pre-wrap;
        }
    </style>

    <script>
        let orderService = null;

        document.addEventListener('DOMContentLoaded', function() {
            waitForFirebase(() => {
                runDiagnostics();
            });
        });

        async function runDiagnostics() {
            const debugContainer = document.getElementById('debug-results');
            
            try {
                // Initialize order service
                addDebugSection('info', 'Step 1: Initializing OrderService');
                orderService = new OrderService();
                await orderService.init();
                addDebugSection('success', 'OrderService initialized successfully', {
                    userId: orderService.userId
                });

                // Get current user
                addDebugSection('info', 'Step 2: Getting current user');
                const user = await orderService.getCurrentUser();
                addDebugSection('success', 'Current user retrieved', {
                    uid: user.uid,
                    email: user.email,
                    emailVerified: user.emailVerified
                });

                // Check Firestore connection
                addDebugSection('info', 'Step 3: Testing Firestore connection');
                const testDoc = await orderService.db.collection('test').limit(1).get();
                addDebugSection('success', 'Firestore connection working', {
                    testResult: testDoc.empty ? 'Empty test collection' : 'Test collection has data'
                });

                // Check orders collection
                addDebugSection('info', 'Step 4: Checking orders collection');
                const allOrdersSnapshot = await orderService.db.collection('orders').limit(5).get();
                addDebugSection('info', 'Orders collection access', {
                    totalOrdersInDb: allOrdersSnapshot.size,
                    sampleOrderIds: allOrdersSnapshot.docs.map(doc => doc.id)
                });

                // Get user's orders with detailed logging
                addDebugSection('info', 'Step 5: Fetching user orders');
                const userOrdersSnapshot = await orderService.db.collection('orders')
                    .where('userId', '==', orderService.userId)
                    .orderBy('createdAt', 'desc')
                    .get();

                addDebugSection('info', 'User orders query result', {
                    userId: orderService.userId,
                    ordersFound: userOrdersSnapshot.size,
                    isEmpty: userOrdersSnapshot.empty
                });

                if (!userOrdersSnapshot.empty) {
                    const orders = [];
                    userOrdersSnapshot.forEach(doc => {
                        const data = doc.data();
                        orders.push({
                            id: doc.id,
                            status: data.status,
                            createdAt: data.createdAt ? data.createdAt.toDate() : 'No timestamp',
                            total: data.total,
                            items: data.items ? data.items.length : 0
                        });
                    });

                    addDebugSection('success', 'User orders found', {
                        orders: orders
                    });

                    // Try to format dates to check for the toLocaleDateString error
                    addDebugSection('info', 'Step 6: Testing date formatting');
                    try {
                        const firstOrder = orders[0];
                        const formattedDate = orderService.formatDateTime(firstOrder.createdAt);
                        addDebugSection('success', 'Date formatting works', {
                            originalDate: firstOrder.createdAt,
                            formattedDate: formattedDate
                        });
                    } catch (dateError) {
                        addDebugSection('error', 'Date formatting failed', {
                            error: dateError.message
                        });
                    }
                } else {
                    addDebugSection('info', 'No orders found for this user', {
                        possibleReasons: [
                            'User has not placed any orders',
                            'Orders are stored with different userId format',
                            'UserId mismatch between auth and orders collection'
                        ]
                    });
                }

                // Check if there are any orders with this user's email
                addDebugSection('info', 'Step 7: Searching by email (fallback)');
                const emailOrdersSnapshot = await orderService.db.collection('orders')
                    .where('shippingInfo.email', '==', user.email)
                    .get();

                addDebugSection('info', 'Email-based search result', {
                    email: user.email,
                    ordersFound: emailOrdersSnapshot.size
                });

            } catch (error) {
                console.error('Diagnostics error:', error);
                addDebugSection('error', 'Diagnostics failed', {
                    error: error.message,
                    stack: error.stack
                });
            }
        }

        function addDebugSection(type, title, data = null) {
            const debugContainer = document.getElementById('debug-results');
            
            const section = document.createElement('div');
            section.className = `debug-section debug-${type}`;
            
            const titleElement = document.createElement('h3');
            titleElement.textContent = title;
            
            section.appendChild(titleElement);
            
            if (data) {
                const preElement = document.createElement('pre');
                preElement.textContent = JSON.stringify(data, null, 2);
                section.appendChild(preElement);
            }
            
            debugContainer.appendChild(section);
        }
    </script>
</body>

</html>