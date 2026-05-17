<?php
/**
 * Get Inventory Audit Trail API Endpoint
 
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/auth.php';
require_once __DIR__ . '/../../php/services/InventoryService.php';

use App\Services\InventoryService;

header('Content-Type: application/json');

// Check authentication
if (!Auth::is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Get query parameters
    $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 500) : 100;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['error' => 'product_id is required']);
        exit;
    }
    
    // Initialize inventory service
    $inventoryService = new InventoryService($pdo);
    
    // Get audit trail
    $result = $inventoryService->getAuditTrail($productId, $limit);
    
    if ($result['success']) {
        // Get total count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM inventory_logs WHERE product_id = ?
        ");
        $stmt->execute([$productId]);
        $countResult = $stmt->fetch(\PDO::FETCH_ASSOC);
        $total = $countResult['total'] ?? 0;
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'product_id' => $productId,
            'logs' => $result['logs'],
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to retrieve audit trail'
        ]);
    }
    
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
    error_log("Inventory audit trail error: " . $e->getMessage());
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error'
    ]);
    error_log("Inventory audit trail error: " . $e->getMessage());
}
