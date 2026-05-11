<?php
/**
 * Seller Messaging Dashboard
 * Shows all conversations with customers
 */
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/auth.php';

// Verify seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header('Location: ' . SITE_URL . 'login.php');
    exit;
}

$seller_id = $_SESSION['user_id'];
$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : null;

// Handle message submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $conversation_id) {
    $message = trim($_POST['message']);
    if (!empty($message) && strlen($message) <= 5000) {
        try {
            $conn = Database::get_connection();
            
            // Get conversation details
            $stmt = $conn->prepare("SELECT * FROM conversations WHERE id = ? AND seller_id = ?");
            $stmt->execute([$conversation_id, $seller_id]);
            $conv = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($conv) {
                $stmt = $conn->prepare("
                    INSERT INTO messages (conversation_id, sender_id, receiver_id, product_id, message_text) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$conversation_id, $seller_id, $conv['customer_id'], $conv['product_id'], $message]);
                
                // Redirect to refresh
                header("Location: messages.php?conversation_id={$conversation_id}");
                exit;
            }
        } catch (Exception $e) {
            $error = 'Error sending message: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = 'Message cannot be empty or exceed 5000 characters';
    }
}

// Get all conversations for this seller
try {
    $conn = Database::get_connection();
    
    $stmt = $conn->prepare("
        SELECT c.*, p.name as product_name, u.full_name as customer_name,
               COALESCE((SELECT message_text FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1), 'No messages') as last_message,
               COALESCE((SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1), c.created_at) as last_message_time
        FROM conversations c
        JOIN products p ON c.product_id = p.id
        JOIN users u ON c.customer_id = u.id
        WHERE c.seller_id = ?
        ORDER BY last_message_time DESC
    ");
    $stmt->execute([$seller_id]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get current conversation if selected
    $current_conversation = null;
    $messages = [];
    
    if ($conversation_id) {
        $stmt = $conn->prepare("SELECT * FROM conversations WHERE id = ? AND seller_id = ?");
        $stmt->execute([$conversation_id, $seller_id]);
        $current_conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($current_conversation) {
            // Get customer name
            $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
            $stmt->execute([$current_conversation['customer_id']]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_conversation['customer_name'] = $customer['full_name'];
            
            // Get product name
            $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
            $stmt->execute([$current_conversation['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_conversation['product_name'] = $product['name'];
            
            // Get all messages
            $stmt = $conn->prepare("SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC");
            $stmt->execute([$conversation_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
} catch (Exception $e) {
    $error = 'Error loading conversations: ' . htmlspecialchars($e->getMessage());
    $conversations = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Seller Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { display: flex; height: 100vh; }
        .sidebar { width: 300px; background: white; border-right: 1px solid #ddd; overflow-y: auto; }
        .main { flex: 1; display: flex; flex-direction: column; }
        .header { background: white; padding: 20px; border-bottom: 1px solid #ddd; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 14px; }
        .conversation-list { padding: 0; }
        .conversation-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s; }
        .conversation-item:hover { background: #f9f9f9; }
        .conversation-item.active { background: #e8f5e9; border-left: 4px solid #28a745; }
        .conversation-item h3 { font-size: 14px; margin-bottom: 5px; }
        .conversation-item p { font-size: 12px; color: #666; margin-bottom: 3px; }
        .conversation-item .time { font-size: 11px; color: #999; }
        .chat-area { flex: 1; display: flex; flex-direction: column; }
        .messages { flex: 1; overflow-y: auto; padding: 20px; background: #fafafa; }
        .message { margin: 10px 0; padding: 12px; border-radius: 8px; max-width: 70%; word-wrap: break-word; }
        .message.sent { background: #28a745; color: white; margin-left: auto; }
        .message.received { background: #e9ecef; }
        .message-time { font-size: 12px; opacity: 0.7; margin-top: 5px; }
        .empty { text-align: center; color: #999; padding: 40px; }
        .input-area { padding: 20px; display: flex; gap: 10px; background: white; border-top: 1px solid #ddd; }
        .input-area input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .input-area button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; margin-bottom: 10px; border-radius: 4px; }
        .no-conversation { display: flex; align-items: center; justify-content: center; height: 100%; color: #999; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #28a745; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar with conversations list -->
        <div class="sidebar">
            <div class="header">
                <h1>💬 Messages</h1>
                <p><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            </div>
            
            <div class="conversation-list">
                <?php if (empty($conversations)): ?>
                    <div class="empty">No conversations yet</div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div class="conversation-item <?php echo $conversation_id == $conv['id'] ? 'active' : ''; ?>" 
                             onclick="window.location.href='messages.php?conversation_id=<?php echo $conv['id']; ?>'">
                            <h3><?php echo htmlspecialchars($conv['customer_name']); ?></h3>
                            <p><strong><?php echo htmlspecialchars($conv['product_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars(substr($conv['last_message'] ?? 'No messages', 0, 50)); ?></p>
                            <div class="time"><?php echo $conv['last_message_time'] ? date('M d, g:i A', strtotime($conv['last_message_time'])) : 'No messages'; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Main chat area -->
        <div class="main">
            <?php if ($current_conversation): ?>
                <div class="header">
                    <h1>💬 Chat with <?php echo htmlspecialchars($current_conversation['customer_name']); ?></h1>
                    <p><strong><?php echo htmlspecialchars($current_conversation['product_name']); ?></strong></p>
                </div>
                
                <div class="messages">
                    <?php if (empty($messages)): ?>
                        <div class="empty">No messages yet</div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['sender_id'] == $seller_id ? 'sent' : 'received'; ?>">
                                <div><?php echo htmlspecialchars($msg['message_text']); ?></div>
                                <div class="message-time"><?php echo date('g:i A', strtotime($msg['created_at'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <form method="POST" class="input-area">
                    <?php if ($error): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <input type="text" name="message" placeholder="Type message..." required autofocus>
                    <button type="submit">Send</button>
                </form>
            <?php else: ?>
                <div class="no-conversation">
                    <p>Select a conversation to start messaging</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Auto-refresh messages every 3 seconds
        <?php if ($conversation_id): ?>
        setInterval(() => {
            fetch('api/get-messages.php?conversation_id=<?php echo $conversation_id; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages) {
                        const messagesDiv = document.querySelector('.messages');
                        const messageElements = messagesDiv.querySelectorAll('.message');
                        
                        // Only update if there are new messages
                        if (data.messages.length > messageElements.length) {
                            location.reload();
                        }
                    }
                })
                .catch(err => console.error('Error refreshing messages:', err));
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
