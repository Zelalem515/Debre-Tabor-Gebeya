<?php
/**
 * Simple Chat API - Send Message
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/messaging.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$conversation_id || !$receiver_id || !$product_id || !$message) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

try {
    $result = Messaging::send_message($conversation_id, $user_id, $receiver_id, $product_id, $message);
    
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => 'Message sent']);
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
