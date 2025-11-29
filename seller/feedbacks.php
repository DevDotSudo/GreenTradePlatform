<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';
include '../includes/functions.php';
ensureUserLoggedIn('seller');

$sellerId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Feedbacks - Green Trade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container mt-4">
        <h1>Feedbacks from Buyers</h1>
        <p>Feedback submitted by buyers about your products and service.</p>

        <div id="feedback-list" class="mt-4">
            <div class="loading">Loading feedbacks...</div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="../assets/js/firebase.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            waitForFirebase(() => loadFeedbacks());
        });

        function formatDate(ts) {
            if (!ts) return '';
            try {
                const d = ts.toDate ? ts.toDate() : new Date(ts);
                return d.toLocaleString();
            } catch (e) { return '' }
        }

        function loadFeedbacks() {
            const sellerId = '<?php echo addslashes($sellerId); ?>';
            const container = document.getElementById('feedback-list');
            container.innerHTML = '<div class="loading">Loading feedbacks...</div>';

            firebase.firestore().collection('feedbacks')
                .where('sellerId', '==', sellerId)
                .orderBy('createdAt', 'desc')
                .get()
                .then(snapshot => {
                    if (snapshot.empty) {
                        container.innerHTML = '<div class="empty-state">No feedbacks yet.</div>';
                        return;
                    }

                    let html = '<div class="feedbacks">';
                    snapshot.forEach(doc => {
                        const f = doc.data();
                        const user = f.userName || 'Buyer';
                        const rating = f.rating || 0;
                        const message = f.message || '';
                        const created = formatDate(f.createdAt);

                        html += `
                            <div class="feedback-card">
                                <div class="feedback-meta">
                                    <strong>${escapeHtml(user)}</strong>
                                    <span class="rating">${rating} / 5</span>
                                    <div class="small text-muted">${escapeHtml(created)}</div>
                                </div>
                                <div class="feedback-message">${escapeHtml(message)}</div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                }).catch(err => {
                    console.error('Error loading feedbacks', err);
                    container.innerHTML = '<div class="alert alert-danger">Failed to load feedbacks.</div>';
                });
        }

        function escapeHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>

    <style>
    .feedback-card { border:1px solid #e6e6e6;padding:12px;border-radius:8px;margin-bottom:12px;background:#fff }
    .feedback-meta { display:flex;align-items:center;justify-content:space-between;gap:12px }
    .feedback-message { margin-top:8px;color:#333 }
    .rating { background:#f1f5f9;padding:4px 8px;border-radius:6px;font-weight:600 }
    </style>
</body>
</html>
