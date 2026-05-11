<?php
/**
 * API Endpoint: Get Unread Message Count
 * 
 * GET /api/messages/unread-count
 */

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../php/auth.php';
require_once __DIR__ . '/../../php/services/MessageService.php';

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
    $messageService = new \App\Services\MessageService();
    
    $unreadCount = $messageService->getUnreadCount(Auth::get_user_id());
    
    echo json_encode([
        'success' => true,
        'unread_count' => $unreadCount
    ]);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
