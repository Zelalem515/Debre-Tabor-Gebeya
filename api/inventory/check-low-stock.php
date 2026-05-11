<?php
/**
 * Check Low Stock Alerts API Endpoint
 * 
 * Checks for products with low stock and creates alerts
 * 
 * Endpoint: POST /api/inventory/check-low-stock.php
 * 
 * Response:
 * {
 *   "success": true,
 *   "alerts_created": 5,
 *   "low_stock_products": 8,
 *   "message": "Low stock check completed"
 * }
 * 
 * Validates: Requirements 3.3
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

// Check if user is admin or seller
$user = Auth::get_current_user();
if (!$user || !in_array($user['role'], ['admin', 'seller'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

try {
    // Initialize inventory service
    $inventoryService = new InventoryService($pdo);
    
    // Check for low stock
    $result = $inventoryService->checkLowStockAlerts();
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'alerts_created' => $result['alerts_created'],
            'low_stock_products' => $result['low_stock_products'],
            'message' => 'Low stock check completed'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Low stock check failed'
        ]);
    }
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error'
    ]);
    error_log("Low stock check error: " . $e->getMessage());
}
