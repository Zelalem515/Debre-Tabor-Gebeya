<?php
/**
 * NotificationService - User Notification System
 * 
 * Handles all notification operations including:
 * - Creating notifications
 * - Retrieving notifications
 * - Marking as read
 * - Managing notification preferences
 * - Sending email/SMS notifications
 */

namespace App\Services;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

class NotificationService {
    
    private $pdo;
    
    /**
     * Initialize NotificationService
     */
    public function __construct($pdo = null) {
        if ($pdo === null) {
            $this->pdo = Database::get_connection();
        } else {
            $this->pdo = $pdo;
        }
    }
    
    /**
     * Create a notification
     * 
     * @param int $userId User ID
     * @param string $type Notification type (message, order_status, delivery, etc.)
     * @param string $title Notification title
     * @param string $message Notification message
     * @param int $orderId Optional order ID
     * @param int $productId Optional product ID
     * @param int $messageId Optional message ID
     * @return array Result with notification ID
     */
    public function createNotification($userId, $type, $title, $message, $orderId = null, $productId = null, $messageId = null) {
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
            
            $notificationId = $this->pdo->lastInsertId();
            
            // Send email notification if enabled
            $this->sendEmailNotification($userId, $type, $title, $message);
            
            return [
                'success' => true,
                'notification_id' => $notificationId
            ];
            
        } catch (\Exception $e) {
            error_log("Create notification error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create notification'];
        }
    }
    
    /**
     * Get notifications for a user
     * 
     * @param int $userId User ID
     * @param int $page Page number
     * @param int $limit Items per page
     * @param bool $unreadOnly Get only unread notifications
     * @return array List of notifications
     */
    public function getNotifications($userId, $page = 1, $limit = 20, $unreadOnly = false) {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT * FROM notifications WHERE user_id = ?";
            $params = [$userId];
            
            if ($unreadOnly) {
                $query .= " AND is_read = FALSE";
            }
            
            $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            error_log("Get notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unread notification count
     * 
     * @param int $userId User ID
     * @return int Unread count
     */
    public function getUnreadCount($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE"
            );
            
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result['count'] ?? 0;
            
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     * @param int $userId User ID
     * @return array Result
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?"
            );
            
            $stmt->execute([$notificationId, $userId]);
            
            return ['success' => true, 'message' => 'Notification marked as read'];
            
        } catch (\Exception $e) {
            error_log("Mark as read error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to mark as read'];
        }
    }
    
    /**
     * Mark all notifications as read
     * 
     * @param int $userId User ID
     * @return array Result
     */
    public function markAllAsRead($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE"
            );
            
            $stmt->execute([$userId]);
            
            return ['success' => true, 'message' => 'All notifications marked as read'];
            
        } catch (\Exception $e) {
            error_log("Mark all as read error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to mark all as read'];
        }
    }
    
    /**
     * Delete a notification
     * 
     * @param int $notificationId Notification ID
     * @param int $userId User ID
     * @return array Result
     */
    public function deleteNotification($notificationId, $userId) {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM notifications WHERE id = ? AND user_id = ?"
            );
            
            $stmt->execute([$notificationId, $userId]);
            
            return ['success' => true, 'message' => 'Notification deleted'];
            
        } catch (\Exception $e) {
            error_log("Delete notification error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete notification'];
        }
    }
    
    /**
     * Get notification preferences for a user
     * 
     * @param int $userId User ID
     * @return array Preferences
     */
    public function getPreferences($userId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM notification_preferences WHERE user_id = ?"
            );
            
            $stmt->execute([$userId]);
            $prefs = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Return default preferences if not found
            if (!$prefs) {
                return [
                    'user_id' => $userId,
                    'email_messages' => true,
                    'email_orders' => true,
                    'email_promotions' => false,
                    'sms_messages' => false,
                    'sms_orders' => false,
                    'in_app_notifications' => true
                ];
            }
            
            return $prefs;
            
        } catch (\Exception $e) {
            error_log("Get preferences error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update notification preferences
     * 
     * @param int $userId User ID
     * @param array $preferences Preferences to update
     * @return array Result
     */
    public function updatePreferences($userId, $preferences) {
        try {
            // Check if preferences exist
            $stmt = $this->pdo->prepare(
                "SELECT id FROM notification_preferences WHERE user_id = ?"
            );
            $stmt->execute([$userId]);
            $exists = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($exists) {
                // Update existing
                $updates = [];
                $params = [];
                
                foreach ($preferences as $key => $value) {
                    $updates[] = "$key = ?";
                    $params[] = $value ? 1 : 0;
                }
                
                $params[] = $userId;
                
                $query = "UPDATE notification_preferences SET " . implode(", ", $updates) . " WHERE user_id = ?";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($params);
            } else {
                // Insert new
                $stmt = $this->pdo->prepare(
                    "INSERT INTO notification_preferences (user_id, email_messages, email_orders, email_promotions, sms_messages, sms_orders, in_app_notifications)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                
                $stmt->execute([
                    $userId,
                    $preferences['email_messages'] ?? true,
                    $preferences['email_orders'] ?? true,
                    $preferences['email_promotions'] ?? false,
                    $preferences['sms_messages'] ?? false,
                    $preferences['sms_orders'] ?? false,
                    $preferences['in_app_notifications'] ?? true
                ]);
            }
            
            return ['success' => true, 'message' => 'Preferences updated'];
            
        } catch (\Exception $e) {
            error_log("Update preferences error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to update preferences'];
        }
    }
    
    /**
     * Send email notification
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $title Title
     * @param string $message Message
     */
    private function sendEmailNotification($userId, $type, $title, $message) {
        try {
            // Get user email
            $stmt = $this->pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) return;
            
            // Get user preferences
            $prefs = $this->getPreferences($userId);
            
            // Check if email notifications are enabled for this type
            $emailKey = 'email_' . $type;
            if (!isset($prefs[$emailKey]) || !$prefs[$emailKey]) {
                return;
            }
            
            // Queue email notification
            $this->queueEmailNotification($user['email'], $title, $message);
            
        } catch (\Exception $e) {
            error_log("Send email notification error: " . $e->getMessage());
        }
    }
    
    /**
     * Queue email notification for sending
     * 
     * @param string $email Email address
     * @param string $title Email title
     * @param string $message Email message
     */
    private function queueEmailNotification($email, $title, $message) {
        try {
            // This would integrate with the EmailService
            // For now, we'll just log it
            error_log("Email notification queued: $email - $title");
            
        } catch (\Exception $e) {
            error_log("Queue email error: " . $e->getMessage());
        }
    }
    
    /**
     * Create order status notification
     * 
     * @param int $orderId Order ID
     * @param string $status New status
     * @param string $message Optional message
     */
    public function notifyOrderStatus($orderId, $status, $message = null) {
        try {
            // Get order details
            $stmt = $this->pdo->prepare(
                "SELECT customer_id, total_amount FROM orders WHERE id = ?"
            );
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$order) return;
            
            $title = "Order Status Update";
            $notificationMessage = "Your order #$orderId status has been updated to: $status";
            
            if ($message) {
                $notificationMessage .= "\n$message";
            }
            
            $this->createNotification(
                $order['customer_id'],
                'order_status',
                $title,
                $notificationMessage,
                $orderId
            );
            
        } catch (\Exception $e) {
            error_log("Notify order status error: " . $e->getMessage());
        }
    }
    
    /**
     * Create message notification
     * 
     * @param int $userId User ID
     * @param int $messageId Message ID
     * @param string $senderName Sender name
     */
    public function notifyNewMessage($userId, $messageId, $senderName) {
        try {
            $title = "New Message";
            $message = "$senderName sent you a message";
            
            $this->createNotification(
                $userId,
                'message',
                $title,
                $message,
                null,
                null,
                $messageId
            );
            
        } catch (\Exception $e) {
            error_log("Notify new message error: " . $e->getMessage());
        }
    }
}
