<?php
/**
 * API Endpoint: Get Conversations
 * 
 * GET /api/messages/get-conversations
 * 
 * Parameters:
 * - page (int, optional) - Page number (default: 1)
 * - limit (int, optional) - Items per page (default: 20)
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
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 20);
    
    // Validate pagination
    if ($page < 1) $page = 1;
    if ($limit < 1 || $limit > 100) $limit = 20;
    
    $messageService = new \App\Services\MessageService();
    
    $conversations = $messageService->getConversations(
        Auth::get_user_id(),
        $page,
        $limit
    );
    
    echo json_encode([
        'success' => true,
        'conversations' => $conversations,
        'page' => $page,
        'limit' => $limit
    ]);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
