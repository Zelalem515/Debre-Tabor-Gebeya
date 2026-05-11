<?php
/**
 * Get messages for a conversation via AJAX
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get conversation ID from query parameter
$product_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

if (!$product_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing conversation_id']);
    exit;
}

try {
    $conn = Database::get_connection();
    
    // Get the conversation ID based on product_id and user
    $stmt = $conn->prepare("
        SELECT id FROM conversations 
        WHERE product_id = ? AND (customer_id = ? OR seller_id = ?)
        LIMIT 1
    ");
    $stmt->execute([$product_id, $_SESSION['user_id'], $_SESSION['user_id']]);
    $conv = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$conv) {
        echo json_encode(['success' => false, 'messages' => []]);
        exit;
    }
    
    // Get all messages for this conversation
    $stmt = $conn->prepare("
        SELECT * FROM messages 
        WHERE conversation_id = ? 
        ORDER BY created_at ASC
    ");
    $stmt->execute([$conv['id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
