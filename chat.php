<?php
/**
 * Complete Chat Page - Everything Included
 * No external dependencies needed
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/products.php';
require_once __DIR__ . '/php/messaging.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Create tables if they don't exist
try {
    $sql = file_get_contents(__DIR__ . '/database/setup_messaging.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            Database::execute_query($stmt);
        }
    }
} catch (Exception $e) {
    // Tables might already exist
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 1;

$product = Products::get_product_by_id($product_id);
if (!$product) {
    die('Product not found');
}

$seller_id = $product['seller_id'];

// Get or create conversation
$conv_result = Messaging::get_or_create_conversation($product_id, $user_id, $seller_id);
if (!$conv_result['success']) {
    die('Error creating conversation: ' . $conv_result['message']);
}
$conversation_id = $conv_result['conversation']['id'];

// Get messages
$messages_result = Messaging::get_messages($conversation_id);
$messages = $messages_result['messages'] ?? [];

// Mark as read
Messaging::mark_as_read($conversation_id, $user_id);

// Handle message send
$send_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message_text = trim($_POST['message']);
    if ($message_text) {
        $result = Messaging::send_message($conversation_id, $user_id, $seller_id, $product_id, $message_text);
        if ($result['success']) {
            header('Location: chat.php?product_id=' . $product_id);
            exit;
        } else {
            $send_error = $result['message'];
        }
    }
}

// Refresh messages
$messages_result = Messaging::get_messages($conversation_id);
$messages = $messages_result['messages'] ?? [];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?php echo htmlspecialchars($product['name']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 20px auto; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { font-size: 24px; color: #333; margin-bottom: 5px; }
        .header p { color: #666; font-size: 14px; }
        .chat-box { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .messages { height: 400px; overflow-y: auto; padding: 20px; background: #fafafa; border-bottom: 1px solid #ddd; }
        .message { margin: 10px 0; padding: 12px; border-radius: 8px; max-width: 70%; word-wrap: break-word; }
        .message.sent { background: #28a745; color: white; margin-left: auto; text-align: right; }
        .message.received { background: #e9ecef; color: #333; }
        .message-time { font-size: 12px; opacity: 0.7; margin-top: 5px; }
        .input-area { padding: 20px; display: flex; gap: 10px; }
        .input-area input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .input-area button { padding: 12px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: bold; }
        .input-area button:hover { background: #218838; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 10px; }
        .empty { text-align: center; color: #999; padding: 40px 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #28a745; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="customer/products.php" class="back-link">← Back to Products</a>
        
        <div class="header">
            <h1>💬 Chat</h1>
            <p><strong>Product:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
            <p><strong>Seller:</strong> <?php echo htmlspecialchars($product['seller_name']); ?></p>
        </div>
        
        <div class="chat-box">
            <div class="messages" id="messages">
                <?php if (empty($messages)): ?>
                    <div class="empty">No messages yet. Start the conversation!</div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                            <div><?php echo htmlspecialchars($msg['message_text']); ?></div>
                            <div class="message-time">
                                <?php 
                                $time = new DateTime($msg['created_at']);
                                echo $time->format('g:i A');
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <form method="POST" class="input-area">
                <?php if ($send_error): ?>
                    <div class="error"><?php echo htmlspecialchars($send_error); ?></div>
                <?php endif; ?>
                <input type="text" name="message" placeholder="Type your message..." required autofocus>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
    
    <script>
        // Auto-scroll to bottom
        const messagesDiv = document.getElementById('messages');
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        
        // Auto-refresh every 3 seconds
        setInterval(function() {
            fetch('chat.php?product_id=<?php echo $product_id; ?>')
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMessages = doc.getElementById('messages');
                    if (newMessages) {
                        messagesDiv.innerHTML = newMessages.innerHTML;
                        messagesDiv.scrollTop = messagesDiv.scrollHeight;
                    }
                });
        }, 3000);
    </script>
</body>
</html>
