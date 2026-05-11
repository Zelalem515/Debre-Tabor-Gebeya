<?php
/**
 * Get Notifications API
 * Retrieves unread notifications for the current user
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/messaging.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get unread notifications
    $notifications = Database::fetch_all(
        "SELECT mn.*, c.product_id, c.customer_id, c.seller_id,
                p.name as product_name,
                CASE 
                    WHEN c.customer_id = ? THEN u_seller.full_name
                    ELSE u_customer.full_name
                END as other_user_name,
                (SELECT message_text FROM messages WHERE id = (
                    SELECT MAX(id) FROM messages WHERE conversation_id = c.id
                )) as last_message
         FROM message_notifications mn
         JOIN conversations c ON mn.conversation_id = c.id
         JOIN products p ON c.product_id = p.id
         JOIN users u_customer ON c.customer_id = u_customer.id
         JOIN users u_seller ON c.seller_id = u_seller.id
         WHERE mn.user_id = ? AND mn.unread_count > 0
         ORDER BY mn.updated_at DESC",
        [$user_id, $user_id]
    );
    
    // Format notifications
    $formatted_notifications = [];
    foreach ($notifications as $notif) {
        $formatted_notifications[] = [
            'id' => $notif['id'],
            'conversation_id' => $notif['conversation_id'],
            'product_id' => $notif['product_id'],
            'product_name' => $notif['product_name'],
            'other_user_name' => $notif['other_user_name'],
            'unread_count' => $notif['unread_count'],
            'last_message' => $notif['last_message'],
            'updated_at' => $notif['updated_at']
        ];
    }
    
    // Return response
    echo json_encode([
        'success' => true,
        'notifications' => $formatted_notifications,
        'total_unread' => array_sum(array_column($notifications, 'unread_count'))
    ]);
    
} catch (Exception $e) {
    error_log('Get notifications error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
