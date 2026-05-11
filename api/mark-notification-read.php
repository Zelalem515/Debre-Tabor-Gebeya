<?php
/**
 * Mark Notification Read API
 * Marks a notification as read
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;

// Validate input
if (!$conversation_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID is required']);
    exit;
}

try {
    // Verify user owns this notification
    $notification = Database::fetch_one(
        "SELECT * FROM message_notifications WHERE user_id = ? AND conversation_id = ?",
        [$user_id, $conversation_id]
    );
    
    if (!$notification) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Mark notification as read
    Database::execute_query(
        "UPDATE message_notifications SET unread_count = 0, last_checked_at = NOW() 
         WHERE user_id = ? AND conversation_id = ?",
        [$user_id, $conversation_id]
    );
    
    // Also mark all messages in this conversation as read
    Database::execute_query(
        "UPDATE messages SET is_read = TRUE, read_at = NOW() 
         WHERE conversation_id = ? AND receiver_id = ? AND is_read = FALSE",
        [$conversation_id, $user_id]
    );
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as read'
    ]);
    
} catch (Exception $e) {
    error_log('Mark notification read error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
