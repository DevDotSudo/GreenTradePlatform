<?php
require_once __DIR__ . '/../includes/session.php';
include '../includes/auth.php';
include '../includes/functions.php';
ensureUserLoggedIn('buyer');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Submit Feedback - Green Trade</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container mt-4">
        <h1>Send Feedback</h1>
        <p>Help sellers improve by leaving feedback about a product or your experience.</p>

        <div id="feedback-alert" class="alert d-none" role="alert"></div>

        <form id="feedback-form">
            <div class="form-group">
                <label for="productId">Product (optional)</label>
                <input type="text" id="productId" name="productId" class="form-control" placeholder="Product ID (optional)">
            </div>

            <div class="form-group">
                <label for="sellerId">Seller ID (optional)</label>
                <input type="text" id="sellerId" name="sellerId" class="form-control" placeholder="Seller ID (optional)">
            </div>

            <div class="form-group">
                <label for="rating">Rating</label>
                <select id="rating" name="rating" class="form-control" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" class="form-control" rows="6" placeholder="Write your feedback..." required></textarea>
            </div>

            <div class="form-actions mt-3">
                <button type="submit" id="submit-feedback" class="btn btn-primary">Send Feedback</button>
            </div>
        </form>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="../assets/js/firebase.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            waitForFirebase(() => {
                setupForm();
            });
        });

        function setupForm() {
            const form = document.getElementById('feedback-form');
            const alertBox = document.getElementById('feedback-alert');
            const submitBtn = document.getElementById('submit-feedback');

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';

                const productId = document.getElementById('productId').value.trim();
                const sellerId = document.getElementById('sellerId').value.trim();
                const rating = parseInt(document.getElementById('rating').value, 10) || 5;
                const message = document.getElementById('message').value.trim();

                const userId = '<?php echo $_SESSION['user_id']; ?>';
                const userName = '<?php echo addslashes($_SESSION['name'] ?? ''); ?>';

                firebase.firestore().collection('feedbacks').add({
                    userId: userId,
                    userName: userName,
                    productId: productId || null,
                    sellerId: sellerId || null,
                    rating: rating,
                    message: message,
                    createdAt: firebase.firestore.FieldValue.serverTimestamp()
                }).then(() => {
                    alertBox.className = 'alert alert-success';
                    alertBox.textContent = 'Thank you — your feedback has been submitted.';
                    alertBox.classList.remove('d-none');
                    form.reset();
                }).catch(err => {
                    console.error('Error submitting feedback:', err);
                    alertBox.className = 'alert alert-danger';
                    alertBox.textContent = 'Failed to submit feedback. Please try again later.';
                    alertBox.classList.remove('d-none');
                }).finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Feedback';
                });
            });
        }
    </script>
</body>
</html>
