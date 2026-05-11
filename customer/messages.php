<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/services/MessageService.php';
require_once __DIR__ . '/../php/localization.php';

Auth::require_role('customer');

$current_language = get_language();
$customer_id = Auth::get_user_id();
$customer_name = Auth::get_user_name();

$messageService = new \App\Services\MessageService();

// Get conversations
$conversations = $messageService->getConversations($customer_id, 1, 50);
$unread_count = $messageService->getUnreadCount($customer_id);

// Get selected conversation if provided
$selected_user_id = $_GET['user_id'] ?? null;
$messages = [];
$selected_user = null;

if ($selected_user_id) {
    $messages = $messageService->getMessages($customer_id, (int)$selected_user_id, 1, 100);
    
    // Get selected user info
    $stmt = Database::execute_query("SELECT id, full_name, role FROM users WHERE id = ?", [(int)$selected_user_id]);
    $selected_user = $stmt->fetch(\PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('messages', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <style>
        .messages-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            height: calc(100vh - 200px);
            margin-top: 20px;
        }
        
        .conversations-list {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow-y: auto;
            background: #f9f9f9;
        }
        
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .conversation-item:hover {
            background: #f0f0f0;
        }
        
        .conversation-item.active {
            background: var(--primary-color);
            color: white;
        }
        
        .conversation-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .conversation-preview {
            font-size: 0.9em;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .conversation-item.active .conversation-preview {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .unread-badge {
            display: inline-block;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .chat-window {
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            background: white;
        }
        
        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            background: var(--primary-color);
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .message {
            display: flex;
            margin-bottom: 10px;
        }
        
        .message.sent {
            justify-content: flex-end;
        }
        
        .message.received {
            justify-content: flex-start;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 12px 15px;
            border-radius: 8px;
            word-wrap: break-word;
        }
        
        .message.sent .message-bubble {
            background: var(--primary-color);
            color: white;
        }
        
        .message.received .message-bubble {
            background: #f0f0f0;
            color: #333;
        }
        
        .message-time {
            font-size: 0.8em;
            color: #999;
            margin-top: 5px;
        }
        
        .chat-input-area {
            padding: 15px;
            border-top: 1px solid #ddd;
            background: #f9f9f9;
            border-radius: 0 0 8px 8px;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .input-group textarea {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            resize: vertical;
            min-height: 50px;
            max-height: 100px;
        }
        
        .input-group button {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .input-group button:hover {
            background: var(--secondary-color);
        }
        
        .empty-state {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .messages-container {
                grid-template-columns: 1fr;
            }
            
            .conversations-list {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 20px;">
        <h1><?php echo translate('messages', $current_language); ?></h1>
        
        <?php if (empty($conversations)): ?>
            <div style="text-align: center; padding: 40px;">
                <p><?php echo translate('no_conversations', $current_language); ?></p>
                <p style="color: #999; font-size: 0.9em;">
                    <?php echo translate('start_conversation_by_contacting_seller', $current_language); ?>
                </p>
            </div>
        <?php else: ?>
            <div class="messages-container">
                <!-- Conversations List -->
                <div class="conversations-list">
                    <?php foreach ($conversations as $conv): ?>
                        <div class="conversation-item <?php echo ($selected_user_id == $conv['other_user_id']) ? 'active' : ''; ?>" 
                             onclick="window.location.href='messages.php?user_id=<?php echo $conv['other_user_id']; ?>'">
                            <div class="conversation-name">
                                <?php echo htmlspecialchars($conv['other_user_name']); ?>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="conversation-preview">
                                <?php echo htmlspecialchars(substr($conv['last_message'] ?? 'No messages', 0, 50)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Chat Window -->
                <div class="chat-window">
                    <?php if ($selected_user): ?>
                        <!-- Chat Header -->
                        <div class="chat-header">
                            <h3 style="margin: 0;">
                                <?php echo htmlspecialchars($selected_user['full_name']); ?>
                                <span style="font-size: 0.8em; opacity: 0.8;">
                                    (<?php echo htmlspecialchars($selected_user['role']); ?>)
                                </span>
                            </h3>
                        </div>
                        
                        <!-- Messages -->
                        <div class="chat-messages" id="messagesContainer">
                            <?php foreach ($messages as $msg): ?>
                                <div class="message <?php echo ($msg['sender_id'] == $customer_id) ? 'sent' : 'received'; ?>">
                                    <div>
                                        <div class="message-bubble">
                                            <?php echo htmlspecialchars($msg['message']); ?>
                                        </div>
                                        <div class="message-time">
                                            <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Input Area -->
                        <div class="chat-input-area">
                            <div class="input-group">
                                <textarea id="messageInput" placeholder="<?php echo translate('type_message', $current_language); ?>"></textarea>
                                <button onclick="sendMessage(<?php echo $selected_user['id']; ?>)">
                                    <?php echo translate('send', $current_language); ?>
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div>
                                <p><?php echo translate('select_conversation', $current_language); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script>
        function sendMessage(receiverId) {
            const message = document.getElementById('messageInput').value.trim();
            
            if (!message) {
                alert('<?php echo translate('message_cannot_be_empty', $current_language); ?>');
                return;
            }
            
            fetch('../api/messages/send.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    receiver_id: receiverId,
                    subject: 'Message',
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('messageInput').value = '';
                    // Reload messages
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to send message');
            });
        }
        
        // Auto-scroll to bottom
        const messagesContainer = document.getElementById('messagesContainer');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Allow Enter to send
        document.getElementById('messageInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(<?php echo $selected_user['id'] ?? 0; ?>);
            }
        });
    </script>
    
    <script src="../js/localization.js"></script>
</body>
</html>
