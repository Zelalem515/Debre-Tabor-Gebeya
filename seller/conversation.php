<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Seller Conversation Detail Page
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/messaging.php';
require_once __DIR__ . '/../php/products.php';
require_once __DIR__ . '/../php/localization.php';
require_once __DIR__ . '/../php/db.php';

// Require seller login
Auth::require_role('seller');

$current_language = get_language();
$seller_id = Auth::get_user_id();
$conversation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$conversation_id) {
    header('Location: dashboard.php');
    exit;
}

// Get conversation details
$conversation = Database::fetch_one(
    "SELECT c.*, p.name as product_name, p.price, u.full_name as customer_name, u.email as customer_email
     FROM conversations c
     JOIN products p ON c.product_id = p.id
     JOIN users u ON c.customer_id = u.id
     WHERE c.id = ? AND c.seller_id = ?",
    [$conversation_id, $seller_id]
);

if (!$conversation) {
    header('Location: dashboard.php');
    exit;
}

// Get messages
$messages_result = Messaging::get_messages($conversation_id);
$messages = $messages_result['messages'] ?? [];

// Mark messages as read
Messaging::mark_as_read($conversation_id, $seller_id);
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation - LALIBELA GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/messaging.css">
    <style>
        .conversation-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
        }

        .conversation-main {
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            height: 600px;
        }

        .conversation-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .conversation-header h2 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }

        .back-btn {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        .conversation-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #fafafa;
        }

        .conversation-messages::-webkit-scrollbar {
            width: 6px;
        }

        .conversation-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .conversation-messages::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .message {
            display: flex;
            flex-direction: column;
            max-width: 70%;
            padding: 10px 12px;
            border-radius: 8px;
            word-wrap: break-word;
        }

        .message.sent {
            align-self: flex-end;
            background: #007bff;
            color: white;
        }

        .message.received {
            align-self: flex-start;
            background: #e9ecef;
            color: #333;
        }

        .message-sender {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
            opacity: 0.8;
        }

        .message-text {
            margin: 0;
            font-size: 14px;
            line-height: 1.4;
        }

        .message-time {
            font-size: 11px;
            margin-top: 5px;
            opacity: 0.7;
        }

        .conversation-input {
            padding: 15px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }

        .conversation-input input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .conversation-input input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .conversation-input button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .conversation-input button:hover {
            background: #0056b3;
        }

        .conversation-input button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .conversation-sidebar {
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 20px;
        }

        .sidebar-section {
            margin-bottom: 20px;
        }

        .sidebar-section:last-child {
            margin-bottom: 0;
        }

        .sidebar-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            color: #999;
        }

        .customer-info {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 5px;
            font-size: 14px;
        }

        .customer-info p {
            margin: 8px 0;
        }

        .customer-info strong {
            color: #2c3e50;
        }

        .product-info {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 5px;
            font-size: 14px;
        }

        .product-info p {
            margin: 8px 0;
        }

        .product-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .product-price {
            color: #28a745;
            font-weight: 600;
        }

        .empty-messages {
            text-align: center;
            color: #999;
            padding: 40px 20px;
        }

        @media (max-width: 768px) {
            .conversation-container {
                grid-template-columns: 1fr;
            }

            .conversation-main {
                height: 500px;
            }

            .message {
                max-width: 85%;
            }

            .conversation-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="conversation-container">
        <!-- Main Conversation Area -->
        <div class="conversation-main">
            <div class="conversation-header">
                <h2>💬 <?php echo htmlspecialchars($conversation['customer_name']); ?></h2>
                <a href="dashboard.php" class="back-btn">← Back to Messages</a>
            </div>

            <div class="conversation-messages" id="messagesContainer">
                <?php if (empty($messages)): ?>
                    <div class="empty-messages">
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['sender_id'] === $seller_id ? 'sent' : 'received'; ?>">
                            <?php if ($msg['sender_id'] !== $seller_id): ?>
                                <div class="message-sender"><?php echo htmlspecialchars($msg['sender_name']); ?></div>
                            <?php endif; ?>
                            <p class="message-text"><?php echo htmlspecialchars($msg['message_text']); ?></p>
                            <span class="message-time"><?php echo date('M d, H:i', strtotime($msg['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="conversation-input">
                <input type="text" id="messageInput" placeholder="Type your reply..." autocomplete="off">
                <button onclick="sendMessage(<?php echo $conversation_id; ?>, <?php echo $conversation['customer_id']; ?>, <?php echo $conversation['product_id']; ?>)">Send</button>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="conversation-sidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">Customer</div>
                <div class="customer-info">
                    <p><strong><?php echo htmlspecialchars($conversation['customer_name']); ?></strong></p>
                    <p style="font-size: 12px; color: #666;">
                        📧 <?php echo htmlspecialchars($conversation['customer_email']); ?>
                    </p>
                </div>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">Product</div>
                <div class="product-info">
                    <p class="product-name"><?php echo htmlspecialchars($conversation['product_name']); ?></p>
                    <p class="product-price"><?php echo number_format($conversation['price'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></p>
                    <p style="font-size: 12px; color: #666;">
                        Started: <?php echo date('M d, Y', strtotime($conversation['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../js/localization.js"></script>
    <script src="../js/main.js"></script>
    <script>
        const conversationId = <?php echo $conversation_id; ?>;
        const sellerId = <?php echo $seller_id; ?>;
        let messageRefreshInterval = null;

        function sendMessage(conversationId, customerId, productId) {
            const messageInput = document.getElementById('messageInput');
            const messageText = messageInput.value.trim();

            if (!messageText) {
                return;
            }

            const sendBtn = document.querySelector('.conversation-input button');
            sendBtn.disabled = true;

            const formData = new FormData();
            formData.append('conversation_id', conversationId);
            formData.append('receiver_id', customerId);
            formData.append('product_id', productId);
            formData.append('message', messageText);

            fetch('../api/send-message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    displayMessage(data.data);
                    sendBtn.disabled = false;
                    messageInput.focus();
                } else {
                    alert('Error sending message: ' + data.message);
                    sendBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending message');
                sendBtn.disabled = false;
            });
        }

        function displayMessage(message) {
            const container = document.getElementById('messagesContainer');
            
            // Remove empty message placeholder if exists
            const emptyMsg = container.querySelector('.empty-messages');
            if (emptyMsg) {
                emptyMsg.remove();
            }

            const isSent = message.sender_id === sellerId;
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;

            let html = '';
            if (!isSent && message.sender_name) {
                html += `<div class="message-sender">${escapeHtml(message.sender_name)}</div>`;
            }
            html += `<p class="message-text">${escapeHtml(message.message_text)}</p>`;
            html += `<span class="message-time">${formatTime(message.created_at)}</span>`;

            messageDiv.innerHTML = html;
            container.appendChild(messageDiv);

            // Scroll to bottom
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 0);
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' +
                   date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Enter key to send
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(conversationId, <?php echo $conversation['customer_id']; ?>, <?php echo $conversation['product_id']; ?>);
            }
        });

        // Auto-refresh messages every 3 seconds
        function startAutoRefresh() {
            messageRefreshInterval = setInterval(() => {
                fetch(`../api/get-conversation.php?conversation_id=${conversationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.messages) {
                            const currentMessages = document.querySelectorAll('.message');
                            if (data.messages.length > currentMessages.length) {
                                // New messages received
                                const newMessages = data.messages.slice(currentMessages.length);
                                newMessages.forEach(msg => displayMessage(msg));
                            }
                        }
                    })
                    .catch(error => console.error('Error refreshing messages:', error));
            }, 3000);
        }

        // Start auto-refresh when page loads
        document.addEventListener('DOMContentLoaded', startAutoRefresh);

        // Stop auto-refresh when page unloads
        window.addEventListener('beforeunload', () => {
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
            }
        });
    </script>
</body>
</html>
