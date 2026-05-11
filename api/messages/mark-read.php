<?php
/**
 * API Endpoint: Mark Message as Read
 * 
 * POST /api/messages/mark-read
 * 
 * Parameters:
 * - message_id (int) - Message ID to mark as read
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

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['message_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing message_id']);
    exit;
}

try {
    $messageService = new \App\Services\MessageService();
    
    $result = $messageService->markAsRead(
        (int)$data['message_id'],
        Auth::get_user_id()
    );
    
    echo json_encode($result);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
