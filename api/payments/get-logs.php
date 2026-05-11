<?php
/**
 * Get Payment Logs API Endpoint
 * 
 * Retrieves payment transaction logs and audit trail
 * 
 * Endpoint: GET /api/payments/get-logs.php
 * 
 * Query Parameters:
 * - order_id: Filter by order ID
 * - transaction_id: Filter by transaction ID
 * - status: Filter by payment status (pending, succeeded, failed)
 * - limit: Number of records to return (default: 50)
 * - offset: Pagination offset (default: 0)
 * 
 * Response:
 * {
 *   "success": true,
 *   "logs": [
 *     {
 *       "id": 1,
 *       "order_id": 123,
 *       "transaction_id": "pi_xxx",
 *       "amount": 100.00,
 *       "status": "succeeded",
 *       "payment_method": "card",
 *       "created_at": "2024-01-15 10:30:00",
 *       "updated_at": "2024-01-15 10:30:05"
 *     }
 *   ],
 *   "total": 150,
 *   "limit": 50,
 *   "offset": 0
 * }
 * 
 * Validates: Requirements 1.5, 4.5, 4.6
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/auth.php';

header('Content-Type: application/json');

// Check authentication
if (!Auth::is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Get query parameters
    $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;
    $transactionId = isset($_GET['transaction_id']) ? trim($_GET['transaction_id']) : null;
    $status = isset($_GET['status']) ? trim($_GET['status']) : null;
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Build query
    $query = "SELECT * FROM payments WHERE 1=1";
    $params = [];
    
    if ($orderId) {
        $query .= " AND order_id = ?";
        $params[] = $orderId;
    }
    
    if ($transactionId) {
        $query .= " AND stripe_payment_intent_id = ?";
        $params[] = $transactionId;
    }
    
    if ($status) {
        $query .= " AND status = ?";
        $params[] = $status;
    }
    
    // Get total count
    $countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $countResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $countResult['total'] ?? 0;
    
    // Get paginated results
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mask sensitive data in logs
    foreach ($logs as &$log) {
        // Remove sensitive fields
        unset($log['card_last_four']);
        unset($log['card_brand']);
        unset($log['card_exp_month']);
        unset($log['card_exp_year']);
    }
    
    echo json_encode([
        'success' => true,
        'logs' => $logs,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
    error_log("Payment logs error: " . $e->getMessage());
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error'
    ]);
    error_log("Payment logs error: " . $e->getMessage());
}
