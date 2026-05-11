<?php
/**
 * Get Conversation API
 * Retrieves conversation and messages between customer and seller
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/messaging.php';
require_once __DIR__ . '/../php/products.php';
require_once __DIR__ . '/../php/db.php';

header('Content-Type: application/json');

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

// Validate input
if (!$product_id && !$conversation_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID or Conversation ID is required']);
    exit;
}

try {
    // If conversation_id is provided, use it directly
    if ($conversation_id) {
        // Verify user is part of this conversation
        $conversation = Database::fetch_one(
            "SELECT * FROM conversations WHERE id = ? AND (customer_id = ? OR seller_id = ?)",
            [$conversation_id, $user_id, $user_id]
        );
        
        if (!$conversation) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $product_id = $conversation['product_id'];
    } else {
        // Get product and seller info
        $product = Products::get_product_by_id($product_id);
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        $seller_id = $product['seller_id'];
        
        // Determine if user is customer or seller
        $user_role = $_SESSION['role'] ?? 'customer';
        
        // If user is the seller, they can't message themselves
        if ($user_id == $seller_id && $user_role == 'seller') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You cannot message yourself']);
            exit;
        }
        
        // Get or create conversation
        $conversation_result = Messaging::get_or_create_conversation($product_id, $user_id, $seller_id);
        
        if (!$conversation_result['success']) {
            http_response_code(500);
            echo json_encode($conversation_result);
            exit;
        }
        
        $conversation = $conversation_result['conversation'];
    }
    
    $conversation_id = $conversation['id'];
    
    // Get all messages
    $messages_result = Messaging::get_messages($conversation_id);
    
    if (!$messages_result['success']) {
        http_response_code(500);
        echo json_encode($messages_result);
        exit;
    }
    
    // Mark messages as read
    Messaging::mark_as_read($conversation_id, $user_id);
    
    // Get seller info
    $seller_info = Messaging::get_seller_info($product_id);
    
    // Get product info
    $product = Products::get_product_by_id($product_id);
    
    // Return response
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversation_id,
        'product' => [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price']
        ],
        'seller' => $seller_info['seller'],
        'messages' => $messages_result['messages'] ?? [],
        'current_user_id' => $user_id
    ]);
    
} catch (Exception $e) {
    error_log('Get conversation error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
