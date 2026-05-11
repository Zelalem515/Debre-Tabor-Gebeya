<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Messaging Module
 * 
 * Handles all messaging operations between customers and sellers
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

class Messaging {
    
    /**
     * Get or create a conversation between customer and seller for a product
     * 
     * @param int $product_id Product ID
     * @param int $customer_id Customer user ID
     * @param int $seller_id Seller user ID
     * @return array Conversation data
     */
    public static function get_or_create_conversation($product_id, $customer_id, $seller_id) {
        try {
            // Try to get existing conversation
            $conversation = Database::fetch_one(
                "SELECT * FROM conversations 
                 WHERE product_id = ? AND customer_id = ? AND seller_id = ?",
                [$product_id, $customer_id, $seller_id]
            );
            
            if ($conversation) {
                return ['success' => true, 'conversation' => $conversation];
            }
            
            // Create new conversation
            Database::execute_query(
                "INSERT INTO conversations (product_id, customer_id, seller_id) 
                 VALUES (?, ?, ?)",
                [$product_id, $customer_id, $seller_id]
            );
            
            $conversation_id = Database::last_insert_id();
            
            // Get the newly created conversation
            $conversation = Database::fetch_one(
                "SELECT * FROM conversations WHERE id = ?",
                [$conversation_id]
            );
            
            return ['success' => true, 'conversation' => $conversation, 'created' => true];
        } catch (Exception $e) {
            error_log('Get or create conversation error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating conversation'];
        }
    }
    
    /**
     * Get all messages in a conversation
     * 
     * @param int $conversation_id Conversation ID
     * @return array Array of messages
     */
    public static function get_messages($conversation_id) {
        try {
            $messages = Database::fetch_all(
                "SELECT m.*, 
                        u_sender.full_name as sender_name,
                        u_sender.id as sender_id,
                        u_receiver.full_name as receiver_name,
                        u_receiver.id as receiver_id
                 FROM messages m
                 JOIN users u_sender ON m.sender_id = u_sender.id
                 JOIN users u_receiver ON m.receiver_id = u_receiver.id
                 WHERE m.conversation_id = ?
                 ORDER BY m.created_at ASC",
                [$conversation_id]
            );
            
            return ['success' => true, 'messages' => $messages];
        } catch (Exception $e) {
            error_log('Get messages error: ' . $e->getMessage());
            return ['success' => false, 'messages' => [], 'message' => 'Error fetching messages'];
        }
    }
    
    /**
     * Send a new message
     * 
     * @param int $conversation_id Conversation ID
     * @param int $sender_id Sender user ID
     * @param int $receiver_id Receiver user ID
     * @param int $product_id Product ID
     * @param string $message_text Message content
     * @return array Success/error response
     */
    public static function send_message($conversation_id, $sender_id, $receiver_id, $product_id, $message_text) {
        try {
            // Validate input
            if (empty($message_text) || strlen(trim($message_text)) === 0) {
                return ['success' => false, 'message' => 'Message cannot be empty'];
            }
            
            if (strlen($message_text) > 5000) {
                return ['success' => false, 'message' => 'Message is too long'];
            }
            
            // Insert message
            Database::execute_query(
                "INSERT INTO messages 
                 (conversation_id, sender_id, receiver_id, product_id, message_text) 
                 VALUES (?, ?, ?, ?, ?)",
                [$conversation_id, $sender_id, $receiver_id, $product_id, $message_text]
            );
            
            $message_id = Database::last_insert_id();
            
            // Update conversation's last_message_at
            Database::execute_query(
                "UPDATE conversations SET last_message_at = NOW() WHERE id = ?",
                [$conversation_id]
            );
            
            // Update or create notification for receiver
            $existing_notification = Database::fetch_one(
                "SELECT * FROM message_notifications 
                 WHERE user_id = ? AND conversation_id = ?",
                [$receiver_id, $conversation_id]
            );
            
            if ($existing_notification) {
                Database::execute_query(
                    "UPDATE message_notifications 
                     SET unread_count = unread_count + 1, updated_at = NOW()
                     WHERE user_id = ? AND conversation_id = ?",
                    [$receiver_id, $conversation_id]
                );
            } else {
                Database::execute_query(
                    "INSERT INTO message_notifications 
                     (user_id, conversation_id, unread_count) 
                     VALUES (?, ?, 1)",
                    [$receiver_id, $conversation_id]
                );
            }
            
            // Get the sent message
            $message = Database::fetch_one(
                "SELECT m.*, 
                        u_sender.full_name as sender_name,
                        u_receiver.full_name as receiver_name
                 FROM messages m
                 JOIN users u_sender ON m.sender_id = u_sender.id
                 JOIN users u_receiver ON m.receiver_id = u_receiver.id
                 WHERE m.id = ?",
                [$message_id]
            );
            
            return ['success' => true, 'message' => 'Message sent successfully', 'data' => $message];
        } catch (Exception $e) {
            error_log('Send message error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error sending message'];
        }
    }
    
    /**
     * Mark messages as read
     * 
     * @param int $conversation_id Conversation ID
     * @param int $user_id User ID (receiver)
     * @return array Success/error response
     */
    public static function mark_as_read($conversation_id, $user_id) {
        try {
            // Mark all unread messages as read
            Database::execute_query(
                "UPDATE messages 
                 SET is_read = TRUE, read_at = NOW()
                 WHERE conversation_id = ? AND receiver_id = ? AND is_read = FALSE",
                [$conversation_id, $user_id]
            );
            
            // Reset unread count
            Database::execute_query(
                "UPDATE message_notifications 
                 SET unread_count = 0, last_checked_at = NOW()
                 WHERE user_id = ? AND conversation_id = ?",
                [$user_id, $conversation_id]
            );
            
            return ['success' => true, 'message' => 'Messages marked as read'];
        } catch (Exception $e) {
            error_log('Mark as read error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error marking messages as read'];
        }
    }
    
    /**
     * Get unread message count for a user in a conversation
     * 
     * @param int $user_id User ID
     * @param int $conversation_id Conversation ID
     * @return int Unread count
     */
    public static function get_unread_count($user_id, $conversation_id) {
        try {
            $result = Database::fetch_one(
                "SELECT unread_count FROM message_notifications 
                 WHERE user_id = ? AND conversation_id = ?",
                [$user_id, $conversation_id]
            );
            
            return $result ? $result['unread_count'] : 0;
        } catch (Exception $e) {
            error_log('Get unread count error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get all conversations for a user
     * 
     * @param int $user_id User ID
     * @param int $limit Limit results
     * @param int $offset Offset
     * @return array Array of conversations
     */
    public static function get_user_conversations($user_id, $limit = 20, $offset = 0) {
        try {
            $conversations = Database::fetch_all(
                "SELECT c.*, 
                        p.name as product_name,
                        p.image_path as product_image,
                        CASE 
                            WHEN c.customer_id = ? THEN u_seller.full_name
                            ELSE u_customer.full_name
                        END as other_user_name,
                        CASE 
                            WHEN c.customer_id = ? THEN u_seller.id
                            ELSE u_customer.id
                        END as other_user_id,
                        (SELECT COUNT(*) FROM message_notifications 
                         WHERE user_id = ? AND conversation_id = c.id) as unread_count
                 FROM conversations c
                 JOIN products p ON c.product_id = p.id
                 JOIN users u_customer ON c.customer_id = u_customer.id
                 JOIN users u_seller ON c.seller_id = u_seller.id
                 WHERE c.customer_id = ? OR c.seller_id = ?
                 ORDER BY c.last_message_at DESC
                 LIMIT ? OFFSET ?",
                [$user_id, $user_id, $user_id, $user_id, $user_id, $limit, $offset]
            );
            
            return ['success' => true, 'conversations' => $conversations];
        } catch (Exception $e) {
            error_log('Get user conversations error: ' . $e->getMessage());
            return ['success' => false, 'conversations' => []];
        }
    }
    
    /**
     * Get seller information for a product
     * 
     * @param int $product_id Product ID
     * @return array Seller information
     */
    public static function get_seller_info($product_id) {
        try {
            $seller = Database::fetch_one(
                "SELECT u.id, u.full_name, u.email, u.phone, p.seller_id
                 FROM products p
                 JOIN users u ON p.seller_id = u.id
                 WHERE p.id = ?",
                [$product_id]
            );
            
            return ['success' => true, 'seller' => $seller];
        } catch (Exception $e) {
            error_log('Get seller info error: ' . $e->getMessage());
            return ['success' => false, 'seller' => null];
        }
    }
}

// Helper functions for backward compatibility
function get_or_create_conversation($product_id, $customer_id, $seller_id) {
    return Messaging::get_or_create_conversation($product_id, $customer_id, $seller_id);
}

function get_messages($conversation_id) {
    return Messaging::get_messages($conversation_id);
}

function send_message($conversation_id, $sender_id, $receiver_id, $product_id, $message_text) {
    return Messaging::send_message($conversation_id, $sender_id, $receiver_id, $product_id, $message_text);
}

function mark_as_read($conversation_id, $user_id) {
    return Messaging::mark_as_read($conversation_id, $user_id);
}

function get_unread_count($user_id, $conversation_id) {
    return Messaging::get_unread_count($user_id, $conversation_id);
}

function get_user_conversations($user_id, $limit = 20, $offset = 0) {
    return Messaging::get_user_conversations($user_id, $limit, $offset);
}

function get_seller_info($product_id) {
    return Messaging::get_seller_info($product_id);
}
?>
