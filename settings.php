<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$currentType = $_SESSION['user_type'] ?? 'buyer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Account Settings - Green Trade</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container" style="padding:32px 0">
        <div class="auth-card" style="max-width:800px;margin:0 auto;padding:24px;">
            <h2>Account Settings</h2>
            <p class="text-muted">Update your profile information or delete your account.</p>

            <div id="settings-alert" class="alert d-none"></div>

            <form id="settings-form">
                <div class="form-group">
                    <label for="settings-name">Full name</label>
                    <input id="settings-name" class="form-control" required />
                </div>

                <div class="form-group">
                    <label for="settings-email">Email</label>
                    <input id="settings-email" class="form-control" readonly />
                </div>

                <div class="form-group">
                    <label for="settings-phone">Phone</label>
                    <input id="settings-phone" class="form-control" />
                </div>

                <div class="form-group">
                    <label for="settings-address">Address</label>
                    <textarea id="settings-address" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Account type</label>
                    <div><strong><?= htmlspecialchars($currentType) ?></strong></div>
                </div>

                <div style="display:flex;gap:12px;align-items:center;margin-top:16px;">
                    <button id="save-settings" type="submit" class="btn btn-primary">Save changes</button>
                    <button id="delete-account" type="button" class="btn btn-danger">Delete account</button>
                </div>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-firestore-compat.js"></script>
    <script src="assets/js/dialogs.js"></script>
    <script src="assets/js/firebase.js"></script>
    <script src="assets/js/auth.js"></script>

    <script>
        // Populate form with current user's profile
        async function initSettings() {
            try {
                const user = await getCurrentUser();
                if (!user) {
                    window.location.href = '/login.php';
                    return;
                }

                const profile = await getUserProfile(user.uid).catch(() => null);
                document.getElementById('settings-name').value = profile?.name || '';
                document.getElementById('settings-email').value = profile?.email || user.email || '';
                document.getElementById('settings-phone').value = profile?.phone || '';
                document.getElementById('settings-address').value = profile?.address || '';
            } catch (err) {
                console.error('initSettings error', err);
            }
        }

        document.getElementById('settings-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const saveBtn = document.getElementById('save-settings');
            saveBtn.disabled = true;

            try {
                const user = await getCurrentUser();
                if (!user) throw new Error('Not signed in');

                const uid = user.uid;
                const data = {
                    name: document.getElementById('settings-name').value.trim(),
                    phone: document.getElementById('settings-phone').value.trim(),
                    address: document.getElementById('settings-address').value.trim()
                };

                await updateUserProfile(uid, data);

                // Update PHP session so server-side pages reflect changes
                if (typeof createSession === 'function') {
                    // read existing profile from Firestore to include required fields
                    const profile = await getUserProfile(uid);
                    await createSession(uid, Object.assign({}, profile, data), profile.emailVerified || false, profile.approved || false);
                }

                showToast({ title: 'Saved', message: 'Your profile was updated.', type: 'success' });
            } catch (err) {
                console.error('Save settings error', err);
                showToast({ title: 'Error', message: err.message || 'Failed to save profile.', type: 'error' });
            } finally {
                saveBtn.disabled = false;
            }
        });

        document.getElementById('delete-account').addEventListener('click', async function () {
            showConfirm({
                title: 'Delete Account',
                message: 'Are you sure you want to permanently delete your account? This action cannot be undone.',
                type: 'warning',
                confirmText: 'Yes, Delete Account',
                cancelText: 'No, Keep Account',
                onConfirm: async () => {
                    try {
                        const user = await getCurrentUser();
                        if (!user) throw new Error('Not signed in');

                        const uid = user.uid;

                        // Remove Firestore profile
                        await firebase.firestore().collection('users').doc(uid).delete().catch(err => {
                            // ignore not-found
                            if (err.code !== 'not-found') throw err;
                        });

                        // Attempt to delete Firebase Auth user (may require recent login)
                        await user.delete();

                        // Server-side logout (clears PHP session)
                        await fetch('/logout.php', { method: 'POST' });
                        showToast({ title: 'Deleted', message: 'Your account has been deleted.', type: 'success' });
                        setTimeout(() => { window.location.href = '/'; }, 1200);
                    } catch (err) {
                        console.error('Delete account error', err);
                        if (err && err.code === 'auth/requires-recent-login') {
                            showToast({ title: 'Action Required', message: 'Please sign in again and try deleting your account.', type: 'warning' });
                            return;
                        }
                        showToast({ title: 'Error', message: err.message || 'Failed to delete account.', type: 'error' });
                    }
                }
            });
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', initSettings);
    </script>
</body>
</html>
