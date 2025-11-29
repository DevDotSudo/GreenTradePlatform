<?php
require_once __DIR__ . '/firebase_config.php';

// Notification functions for handling user notifications
function createNotification($userId, $title, $message, $type = 'info', $read = false, $link = null) {
    global $firebaseConfig;

    // This would typically send to Firebase, but for now we'll just return success
    // In a real implementation, you'd use Firebase Admin SDK or similar
    return [
        'success' => true,
        'message' => 'Notification created',
        'data' => [
            'userId' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'read' => $read,
            'link' => $link,
            'createdAt' => date('Y-m-d H:i:s')
        ]
    ];
}

function getUserNotifications($userId, $limit = 50) {
    // Placeholder for getting user notifications
    // In a real implementation, this would query Firebase
    return [
        'success' => true,
        'notifications' => [],
        'count' => 0
    ];
}

function markNotificationAsRead($notificationId) {
    // Placeholder for marking notification as read
    return [
        'success' => true,
        'message' => 'Notification marked as read'
    ];
}