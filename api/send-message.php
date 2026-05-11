<?php
/**
 * Send Message API
 * Sends a new message in a conversation
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/messaging.php';

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
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$message_text = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate input
if (!$conversation_id || !$receiver_id || !$product_id || !$message_text) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Verify conversation exists and user is part of it
    $conversation = Database::fetch_one(
        "SELECT * FROM conversations WHERE id = ? AND (customer_id = ? OR seller_id = ?)",
        [$conversation_id, $user_id, $user_id]
    );
    
    if (!$conversation) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Send message
    $result = Messaging::send_message($conversation_id, $user_id, $receiver_id, $product_id, $message_text);
    
    if (!$result['success']) {
        http_response_code(400);
        echo json_encode($result);
        exit;
    }
    
    // Return success with message data
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'data' => $result['data']
    ]);
    
} catch (Exception $e) {
    error_log('Send message error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
