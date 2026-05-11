<?php
/**
 * Simple Chat API - Get Messages
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/messaging.php';
require_once __DIR__ . '/../php/products.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

try {
    $product = Products::get_product_by_id($product_id);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $seller_id = $product['seller_id'];
    
    // Get or create conversation
    $conv_result = Messaging::get_or_create_conversation($product_id, $user_id, $seller_id);
    if (!$conv_result['success']) {
        echo json_encode(['success' => false, 'message' => 'Error creating conversation']);
        exit;
    }
    
    $conversation_id = $conv_result['conversation']['id'];
    
    // Get messages
    $msg_result = Messaging::get_messages($conversation_id);
    if (!$msg_result['success']) {
        echo json_encode(['success' => false, 'message' => 'Error getting messages']);
        exit;
    }
    
    // Mark as read
    Messaging::mark_as_read($conversation_id, $user_id);
    
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversation_id,
        'current_user_id' => $user_id,
        'messages' => $msg_result['messages'] ?? []
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
