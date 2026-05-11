<?php
/**
 * MessageService - Seller-Customer Messaging System
 * 
 * Handles all messaging operations including:
 * - Sending messages between users
 * - Retrieving conversations
 * - Managing message read status
 * - File attachments
 * - Message history
 * 
 * Use Cases:
 * - Price negotiation: "Is this price negotiable?"
 * - Product clarification: "Does this have warranty?"
 * - Delivery discussion: "Can you deliver by Friday?"
 */

namespace App\Services;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

class MessageService {
    
    private $pdo;
    
    /**
     * Initialize MessageService
     */
    public function __construct($pdo = null) {
        if ($pdo === null) {
            $this->pdo = Database::get_connection();
        } else {
            $this->pdo = $pdo;
        }
    }
    
    /**
     * Send a message from one user to another
     * 
     * @param int $senderId Sender user ID
     * @param int $receiverId Receiver user ID
     * @param string $subject Message subject
     * @param string $message Message content
     * @param int $orderId Optional order ID
     * @param int $productId Optional product ID
     * @return array Result with success status and message ID
     */
    public function sendMessage($senderId, $receiverId, $subject, $message, $orderId = null, $productId = null) {
        try {
            // Validate inputs
            if (empty($senderId) || empty($receiverId)) {
                return ['success' => false, 'error' => 'Sender and receiver IDs are required'];
            }
            
            if (empty($message) || strlen($message) < 1) {
                return ['success' => false, 'error' => 'Message cannot be empty'];
            }
            
            if (strlen($message) > 5000) {
                return ['success' => false, 'error' => 'Message is too long (max 5000 characters)'];
            }
            
            // Prevent self-messaging
            if ($senderId === $receiverId) {
                return ['success' => false, 'error' => 'Cannot send message to yourself'];
            }
            
            // Insert message
            $stmt = $this->pdo->prepare(
                "INSERT INTO messages (sender_id, receiver_id, subject, message, order_id, product_id)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $senderId,
                $receiverId,
                $subject ?: 'No Subject',
                $message,
                $orderId ?: null,
                $productId ?: null
            ]);
            
            $messageId = $this->pdo->lastInsertId();
            
            // Update or create conversation
            $this->updateConversation($senderId, $receiverId, $messageId);
            
            // Create notification for receiver
            $this->createNotification(
                $receiverId,
                'message',
                'New Message',
                "You have a new message from a user",
                null,
                null,
                $messageId
            );
            
            return [
                'success' => true,
                'message_id' => $messageId,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            error_log("Message sending error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to send message'];
        }
    }
    
    /**
     * Get all conversations for a user
     * 
     * @param int $userId User ID
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array List of conversations
     */
    public function getConversations($userId, $page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            
            $stmt = $this->pdo->prepare(
                "SELECT 
                    c.id as conversation_id,
                    CASE 
                        WHEN c.user1_id = ? THEN c.user2_id 
                        ELSE c.user1_id 
                    END as other_user_id,
                    u.full_name as other_user_name,
                    u.role as other_user_role,
                    c.last_message_at,
                    m.message as last_message,
                    (SELECT COUNT(*) FROM messages 
                     WHERE (sender_id = ? AND receiver_id = c.user1_id OR c.user2_id)
                     OR (sender_id = ? AND receiver_id = c.user1_id OR c.user2_id)
                     AND is_read = FALSE) as unread_count
                 FROM conversations c
                 JOIN users u ON (
                    (c.user1_id = ? AND u.id = c.user2_id) OR
                    (c.user2_id = ? AND u.id = c.user1_id)
                 )
                 LEFT JOIN messages m ON m.id = c.last_message_id
                 WHERE c.user1_id = ? OR c.user2_id = ?
                 ORDER BY c.last_message_at DESC
                 LIMIT ? OFFSET ?"
            );
            
            $stmt->execute([
                $userId, $userId, $userId, $userId, $userId, $userId, $userId,
                $limit, $offset
            ]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get conversations error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get messages between two users
     * 
     * @param int $userId1 First user ID
     * @param int $userId2 Second user ID
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array List of messages
     */
    public function getMessages($userId1, $userId2, $page = 1, $limit = 50) {
        try {
            $offset = ($page - 1) * $limit;
            
            $stmt = $this->pdo->prepare(
                "SELECT 
                    m.id,
                    m.sender_id,
                    m.receiver_id,
                    u.full_name as sender_name,
                    u.role as sender_role,
                    m.subject,
                    m.message,
                    m.order_id,
                    m.product_id,
                    m.is_read,
                    m.created_at,
                    (SELECT COUNT(*) FROM message_attachments WHERE message_id = m.id) as attachment_count
                 FROM messages m
                 JOIN users u ON u.id = m.sender_id
                 WHERE (m.sender_id = ? AND m.receiver_id = ?)
                    OR (m.sender_id = ? AND m.receiver_id = ?)
                 ORDER BY m.created_at DESC
                 LIMIT ? OFFSET ?"
            );
            
            $stmt->execute([$userId1, $userId2, $userId2, $userId1, $limit, $offset]);
            
            $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Reverse to show oldest first
            return array_reverse($messages);
            
        } catch (\Exception $e) {
            error_log("Get messages error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mark message as read
     * 
     * @param int $messageId Message ID
     * @param int $userId User ID (receiver)
     * @return array Result
     */
    public function markAsRead($messageId, $userId) {
        try {
            // Update message read status
            $stmt = $this->pdo->prepare(
                "UPDATE messages SET is_read = TRUE WHERE id = ? AND receiver_id = ?"
            );
            
            $stmt->execute([$messageId, $userId]);
            
            // Record read receipt
            $stmt = $this->pdo->prepare(
                "INSERT INTO message_read_receipts (message_id, read_by)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE read_at = NOW()"
            );
            
            $stmt->execute([$messageId, $userId]);
            
            return ['success' => true, 'message' => 'Message marked as read'];
            
        } catch (\Exception $e) {
            error_log("Mark as read error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to mark message as read'];
        }
    }
    
    /**
     * Get unread message count for a user
     * 
     * @param int $userId User ID
     * @return int Unread count
     */
    public function getUnreadCount($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) as count FROM messages 
                 WHERE receiver_id = ? AND is_read = FALSE"
            );
            
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result['count'] ?? 0;
            
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Delete a message
     * 
     * @param int $messageId Message ID
     * @param int $userId User ID (sender)
     * @return array Result
     */
    public function deleteMessage($messageId, $userId) {
        try {
            // Only sender can delete
            $stmt = $this->pdo->prepare(
                "DELETE FROM messages WHERE id = ? AND sender_id = ?"
            );
            
            $stmt->execute([$messageId, $userId]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Message deleted'];
            } else {
                return ['success' => false, 'error' => 'Message not found or unauthorized'];
            }
            
        } catch (\Exception $e) {
            error_log("Delete message error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete message'];
        }
    }
    
    /**
     * Search messages
     * 
     * @param int $userId User ID
     * @param string $query Search query
     * @param int $limit Results limit
     * @return array Search results
     */
    public function searchMessages($userId, $query, $limit = 20) {
        try {
            $searchTerm = '%' . $query . '%';
            
            $stmt = $this->pdo->prepare(
                "SELECT 
                    m.id,
                    m.sender_id,
                    m.receiver_id,
                    u.full_name as sender_name,
                    m.subject,
                    m.message,
                    m.created_at
                 FROM messages m
                 JOIN users u ON u.id = m.sender_id
                 WHERE (m.sender_id = ? OR m.receiver_id = ?)
                    AND (m.subject LIKE ? OR m.message LIKE ?)
                 ORDER BY m.created_at DESC
                 LIMIT ?"
            );
            
            $stmt->execute([$userId, $userId, $searchTerm, $searchTerm, $limit]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Search messages error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get message details with attachments
     * 
     * @param int $messageId Message ID
     * @return array Message details
     */
    public function getMessageDetails($messageId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT 
                    m.*,
                    u.full_name as sender_name,
                    u.role as sender_role
                 FROM messages m
                 JOIN users u ON u.id = m.sender_id
                 WHERE m.id = ?"
            );
            
            $stmt->execute([$messageId]);
            $message = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($message) {
                // Get attachments
                $stmt = $this->pdo->prepare(
                    "SELECT * FROM message_attachments WHERE message_id = ?"
                );
                
                $stmt->execute([$messageId]);
                $message['attachments'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            return $message;
            
        } catch (\Exception $e) {
            error_log("Get message details error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update conversation with latest message
     * 
     * @param int $user1Id First user ID
     * @param int $user2Id Second user ID
     * @param int $messageId Message ID
     */
    private function updateConversation($user1Id, $user2Id, $messageId) {
        try {
            // Ensure user1Id < user2Id for consistency
            if ($user1Id > $user2Id) {
                $temp = $user1Id;
                $user1Id = $user2Id;
                $user2Id = $temp;
            }
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO conversations (user1_id, user2_id, last_message_id, last_message_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE 
                 last_message_id = ?, last_message_at = NOW()"
            );
            
            $stmt->execute([$user1Id, $user2Id, $messageId, $messageId]);
            
        } catch (\Exception $e) {
            error_log("Update conversation error: " . $e->getMessage());
        }
    }
    
    /**
     * Create a notification
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $title Notification title
     * @param string $message Notification message
     * @param int $orderId Optional order ID
     * @param int $productId Optional product ID
     * @param int $messageId Optional message ID
     */
    private function createNotification($userId, $type, $title, $message, $orderId = null, $productId = null, $messageId = null) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO notifications (user_id, type, title, message, related_order_id, related_product_id, related_message_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $userId,
                $type,
                $title,
                $message,
                $orderId,
                $productId,
                $messageId
            ]);
            
        } catch (\Exception $e) {
            error_log("Create notification error: " . $e->getMessage());
        }
    }
}
