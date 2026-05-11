<?php
/**
 * Messaging System - Complete Working Implementation
 * This file handles all chat functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Load configuration
try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/php/db.php';
    require_once __DIR__ . '/php/auth.php';
    require_once __DIR__ . '/php/products.php';
} catch (Exception $e) {
    die('Configuration error: ' . htmlspecialchars($e->getMessage()));
}

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 1;

// Get product details
try {
    $product = Products::get_product_by_id($product_id);
    if (!$product) {
        die('Product not found');
    }
} catch (Exception $e) {
    die('Error loading product: ' . htmlspecialchars($e->getMessage()));
}

$seller_id = $product['seller_id'];

// Initialize database tables
try {
    initializeTables();
} catch (Exception $e) {
    die('Error initializing tables: ' . htmlspecialchars($e->getMessage()));
}

// Get or create conversation
try {
    $conversation_id = getOrCreateConversation($product_id, $user_id, $seller_id);
} catch (Exception $e) {
    die('Error creating conversation: ' . htmlspecialchars($e->getMessage()));
}

// Handle message submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message) && strlen($message) <= 5000) {
        try {
            if (sendMessage($conversation_id, $user_id, $seller_id, $product_id, $message)) {
                header("Location: messaging.php?product_id={$product_id}");
                exit;
            } else {
                $error = 'Failed to send message';
            }
        } catch (Exception $e) {
            $error = 'Error sending message: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = 'Message cannot be empty or exceed 5000 characters';
    }
}

// Get all messages
try {
    $messages = getMessages($conversation_id);
} catch (Exception $e) {
    $messages = [];
    $error = 'Error loading messages: ' . htmlspecialchars($e->getMessage());
}

// Helper functions
function initializeTables() {
    try {
        // Get connection directly to avoid exception swallowing
        $conn = Database::get_connection();
        
        // Conversations table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS conversations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                product_id INT NOT NULL,
                customer_id INT NOT NULL,
                seller_id INT NOT NULL,
                last_message_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
                
                INDEX idx_product_id (product_id),
                INDEX idx_customer_id (customer_id),
                INDEX idx_seller_id (seller_id),
                
                UNIQUE KEY unique_conversation (product_id, customer_id, seller_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // Messages table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS messages (
                id INT PRIMARY KEY AUTO_INCREMENT,
                conversation_id INT NOT NULL,
                sender_id INT NOT NULL,
                receiver_id INT NOT NULL,
                product_id INT NOT NULL,
                message_text TEXT NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                read_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                
                INDEX idx_conversation_id (conversation_id),
                INDEX idx_sender_id (sender_id),
                INDEX idx_receiver_id (receiver_id),
                INDEX idx_product_id (product_id),
                INDEX idx_is_read (is_read),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    } catch (Exception $e) {
        error_log('Table initialization error: ' . $e->getMessage());
        throw $e;
    }
}

function getOrCreateConversation($product_id, $customer_id, $seller_id) {
    try {
        $conn = Database::get_connection();
        
        // Check if conversation exists
        $stmt = $conn->prepare("SELECT id FROM conversations WHERE product_id = ? AND customer_id = ? AND seller_id = ?");
        $stmt->execute([$product_id, $customer_id, $seller_id]);
        $conv = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($conv) {
            return $conv['id'];
        }
        
        // Create new conversation
        $stmt = $conn->prepare("INSERT INTO conversations (product_id, customer_id, seller_id) VALUES (?, ?, ?)");
        $stmt->execute([$product_id, $customer_id, $seller_id]);
        
        return $conn->lastInsertId();
    } catch (Exception $e) {
        error_log('Conversation error: ' . $e->getMessage());
        throw $e;
    }
}

function sendMessage($conversation_id, $sender_id, $receiver_id, $product_id, $message_text) {
    try {
        $conn = Database::get_connection();
        $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, product_id, message_text) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$conversation_id, $sender_id, $receiver_id, $product_id, $message_text]);
        return true;
    } catch (Exception $e) {
        error_log('Send message error: ' . $e->getMessage());
        throw $e;
    }
}

function getMessages($conversation_id) {
    try {
        $conn = Database::get_connection();
        $stmt = $conn->prepare("SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC");
        $stmt->execute([$conversation_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Get messages error: ' . $e->getMessage());
        return [];
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin-bottom: 10px; }
        .header p { color: #666; font-size: 14px; }
        .chat-box { background: white; border-radius: 8px; overflow: hidden; }
        .messages { height: 400px; overflow-y: auto; padding: 20px; background: #fafafa; border-bottom: 1px solid #ddd; }
        .message { margin: 10px 0; padding: 12px; border-radius: 8px; max-width: 70%; word-wrap: break-word; }
        .message.sent { background: #28a745; color: white; margin-left: auto; }
        .message.received { background: #e9ecef; }
        .message-time { font-size: 12px; opacity: 0.7; margin-top: 5px; }
        .empty { text-align: center; color: #999; padding: 40px; }
        .input-area { padding: 20px; display: flex; gap: 10px; }
        .input-area input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .input-area button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; margin-bottom: 10px; border-radius: 4px; }
        .back { display: inline-block; margin-bottom: 20px; color: #28a745; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <a href="customer/products.php" class="back">← Back</a>
        
        <div class="header">
            <h1>💬 Chat</h1>
            <p><strong><?php echo htmlspecialchars($product['name']); ?></strong></p>
            <p>Seller: <?php echo htmlspecialchars($product['seller_name']); ?></p>
        </div>
        
        <div class="chat-box">
            <div class="messages">
                <?php if (empty($messages)): ?>
                    <div class="empty">No messages yet</div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
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
        </div>
    </div>
    
    <script>
        // Auto-refresh messages every 3 seconds without reloading the page
        function refreshMessages() {
            const conversationId = new URLSearchParams(window.location.search).get('product_id');
            
            fetch('api/get-messages.php?conversation_id=' + conversationId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages) {
                        const messagesDiv = document.querySelector('.messages');
                        
                        // Clear existing messages
                        const messageElements = messagesDiv.querySelectorAll('.message');
                        messageElements.forEach(el => el.remove());
                        
                        // Add new messages
                        if (data.messages.length === 0) {
                            messagesDiv.innerHTML = '<div class="empty">No messages yet</div>';
                        } else {
                            data.messages.forEach(msg => {
                                const messageDiv = document.createElement('div');
                                const isSent = msg.sender_id == <?php echo $user_id; ?>;
                                messageDiv.className = 'message ' + (isSent ? 'sent' : 'received');
                                messageDiv.innerHTML = `
                                    <div>${escapeHtml(msg.message_text)}</div>
                                    <div class="message-time">${new Date(msg.created_at).toLocaleTimeString()}</div>
                                `;
                                messagesDiv.appendChild(messageDiv);
                            });
                            // Scroll to bottom
                            messagesDiv.scrollTop = messagesDiv.scrollHeight;
                        }
                    }
                })
                .catch(err => console.error('Error refreshing messages:', err));
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Refresh messages every 3 seconds
        setInterval(refreshMessages, 3000);
        
        // Initial refresh
        refreshMessages();
    </script>
</body>
</html>
