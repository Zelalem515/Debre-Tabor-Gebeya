<?php
/**
 * API Endpoint: Get Messages
 * 
 * GET /api/messages/get-messages
 * 
 * Parameters:
 * - user_id (int) - Other user ID
 * - page (int, optional) - Page number (default: 1)
 * - limit (int, optional) - Items per page (default: 50)
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

// Validate required parameters
if (empty($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing user_id parameter']);
    exit;
}

try {
    $otherUserId = (int)$_GET['user_id'];
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($limit < 1 || $limit > 100) $limit = 50;
    
    $messageService = new \App\Services\MessageService();
    
    $messages = $messageService->getMessages(
        Auth::get_user_id(),
        $otherUserId,
        $page,
        $limit
    );
    
    // Mark all messages as read
    foreach ($messages as $msg) {
        if (!$msg['is_read'] && $msg['receiver_id'] == Auth::get_user_id()) {
            $messageService->markAsRead($msg['id'], Auth::get_user_id());
        }
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'page' => $page,
        'limit' => $limit
    ]);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
