<?php
/**
 * API Endpoint: Mark Notification as Read
 * 
 * POST /api/notifications/mark-read
 * 
 * Parameters:
 * - notification_id (int) - Notification ID
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

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['notification_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing notification_id']);
    exit;
}

try {
    $notificationService = new \App\Services\NotificationService();
    
    $result = $notificationService->markAsRead(
        (int)$data['notification_id'],
        Auth::get_user_id()
    );
    
    echo json_encode($result);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
