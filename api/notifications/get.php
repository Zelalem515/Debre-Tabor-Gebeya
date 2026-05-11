<?php
/**
 * API Endpoint: Get Notifications
 * 
 * GET /api/notifications/get
 * 
 * Parameters:
 * - page (int, optional) - Page number (default: 1)
 * - limit (int, optional) - Items per page (default: 20)
 * - unread_only (bool, optional) - Get only unread (default: false)
 */

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../php/auth.php';
require_once __DIR__ . '/../../php/services/NotificationService.php';

// Check authentication
if (!Auth::is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($limit < 1 || $limit > 100) $limit = 20;
    
    $notificationService = new \App\Services\NotificationService();
    
    $notifications = $notificationService->getNotifications(
        Auth::get_user_id(),
        $page,
        $limit,
        $unreadOnly
    );
    
    $unreadCount = $notificationService->getUnreadCount(Auth::get_user_id());
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unreadCount,
        'page' => $page,
        'limit' => $limit
    ]);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
