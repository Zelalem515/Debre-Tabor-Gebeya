<?php
/**
 * InventoryService - Inventory Management System
 * 
 * Handles all inventory-related operations including:
 * - Stock level tracking
 * - Inventory reservation (atomic operations)
 * - Inventory release on cancellation
 * - Low-stock detection and alerts
 * - Inventory logging and audit trail
 * 
 * Property Tests:
 * - Property 2: Inventory Atomicity
 * - Property 3: Stock Consistency
 * - Property 4: Overselling Prevention
 * - Property 5: Cancellation Restoration
 * - Property 9: Low Stock Alert Accuracy
 * - Property 11: Audit Trail Completeness
 */

namespace App\Services;

require_once __DIR__ . '/../../config.php';

use PDO;

class InventoryService {
    
    private $pdo;
    private $logger;
    
    // Stock status constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_RESERVED = 'reserved';
    const STATUS_SOLD = 'sold';
    
    /**
     * Initialize InventoryService
     */
    public function __construct($pdo = null) {
        $this->pdo = $pdo;
        $this->logger = new InventoryLogger($pdo);
    }
    
    /**
     * Get stock level for a product
     * 
     * @param int $productId Product ID
     * @return array Stock level information
     */
    public function getStockLevel($productId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    id,
                    product_id,
                    available_stock,
                    reserved_stock,
                    sold_stock,
                    total_stock,
                    low_stock_threshold,
                    last_updated
                FROM inventory
                WHERE product_id = ?
            ");
            
            $stmt->execute([$productId]);
            $inventory = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$inventory) {
                return [
                    'success' => false,
                    'error' => 'Product not found in inventory'
                ];
            }
            
            return [
                'success' => true,
                'product_id' => $productId,
                'available_stock' => (int)$inventory['available_stock'],
                'reserved_stock' => (int)$inventory['reserved_stock'],
                'sold_stock' => (int)$inventory['sold_stock'],
                'total_stock' => (int)$inventory['total_stock'],
                'low_stock_threshold' => (int)$inventory['low_stock_threshold'],
                'is_low_stock' => (int)$inventory['available_stock'] <= (int)$inventory['low_stock_threshold'],
                'last_updated' => $inventory['last_updated']
            ];
            
        } catch (\PDOException $e) {
            $this->logger->logError('getStockLevel', $e->getMessage());
            return [
                'success' => false,
                'error' => 'Database error'
            ];
        }
    }
    
    /**
     * Check if product is in stock
     * 
     * @param int $productId Product ID
     * @param int $quantity Quantity needed
     * @return bool True if in stock
     */
    public function isInStock($productId, $quantity = 1) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT available_stock FROM inventory WHERE product_id = ?
            ");
            
            $stmt->execute([$productId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return false;
            }
            
            return (int)$result['available_stock'] >= $quantity;
            
        } catch (\PDOException $e) {
            $this->logger->logError('isInStock', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update stock level
     * 
     * @param int $productId Product ID
     * @param int $quantity Quantity change (positive or negative)
     * @param string $reason Reason for update
     * @param int $userId User ID making the change
     * @return array Update result
     */
    public function updateStock($productId, $quantity, $reason = 'manual_adjustment', $userId = null) {
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Get current stock
            $stmt = $this->pdo->prepare("
                SELECT available_stock FROM inventory WHERE product_id = ? FOR UPDATE
            ");
            $stmt->execute([$productId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'error' => 'Product not found'
                ];
            }
            
            $currentStock = (int)$result['available_stock'];
            $newStock = $currentStock + $quantity;
            
            // Prevent negative stock
            if ($newStock < 0) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'error' => 'Insufficient stock for this operation'
                ];
            }
            
            // Update inventory
            $stmt = $this->pdo->prepare("
                UPDATE inventory
                SET available_stock = ?,
                    total_stock = total_stock + ?,
                    last_updated = NOW()
                WHERE product_id = ?
            ");
            $stmt->execute([$newStock, $quantity, $productId]);
            
            // Log the change
            $this->logger->logInventoryChange(
                $productId,
                $quantity,
                $currentStock,
                $newStock,
                $reason,
                $userId
            );
            
            // Commit transaction
            $this->pdo->commit();
            
            return [
                'success' => true,
                'product_id' => $productId,
                'previous_stock' => $currentStock,
                'new_stock' => $newStock,
                'quantity_changed' => $quantity,
                'reason' => $reason
            ];
            
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            $this->logger->logError('updateStock', $e->getMessage());
            return [
                'success' => false,
                'error' => 'Database error'
            ];
        }
    }
    
    /**
     * Reserve inventory for an order
     * 
     * Property 2: Inventory Atomicity
     * - Both payment and reservation succeed or both fail
     * 
     * Property 4: Overselling Prevention
     * - Reject purchases when stock insufficient
     * 
     * @param int $orderId Order ID
     * @param array $items Items to reserve [product_id => quantity]
     * @return array Reservation result
     */
    public function reserveInventory($orderId, $items) {
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            $reservedItems = [];
            
            foreach ($items as $productId => $quantity) {
                // Get current stock with lock
                $stmt = $this->pdo->prepare("
                    SELECT available_stock, reserved_stock FROM inventory 
                    WHERE product_id = ? FOR UPDATE
                ");
                $stmt->execute([$productId]);
                $inventory = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$inventory) {
                    $this->pdo->rollBack();
                    return [
                        'success' => false,
                        'error' => "Product {$productId} not found in inventory"
                    ];
                }
                
                $availableStock = (int)$inventory['available_stock'];
                
                // Check if enough stock available
                if ($availableStock < $quantity) {
                    $this->pdo->rollBack();
                    return [
                        'success' => false,
                        'error' => "Insufficient stock for product {$productId}. Available: {$availableStock}, Requested: {$quantity}"
                    ];
                }
                
                // Reserve stock
                $stmt = $this->pdo->prepare("
                    UPDATE inventory
                    SET available_stock = available_stock - ?,
                        reserved_stock = reserved_stock + ?,
                        last_updated = NOW()
                    WHERE product_id = ?
                ");
                $stmt->execute([$quantity, $quantity, $productId]);
                
                // Create reservation record
                $stmt = $this->pdo->prepare("
                    INSERT INTO inventory_reservations (order_id, product_id, quantity, status, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$orderId, $productId, $quantity, 'reserved']);
                
                // Log reservation
                $this->logger->logInventoryChange(
                    $productId,
                    -$quantity,
                    $availableStock,
                    $availableStock - $quantity,
                    'order_reservation',
                    null,
                    $orderId
                );
                
                $reservedItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity
                ];
            }
            
            // Commit transaction
            $this->pdo->commit();
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'reserved_items' => $reservedItems,
                'message' => 'Inventory reserved successfully'
            ];
            
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            $this->logger->logError('reserveInventory', $e->getMessage());
            return [
                'success' => false,
                'error' => 'Database error during reservation'
            ];
        }
    }
    
    /**
     * Release reserved inventory on cancellation
     * 
     * Property 5: Cancellation Restoration
     * - Cancelled orders restore stock
     * 
     * @param int $orderId Order ID
     * @return array Release result
     */
    public function releaseInventory($orderId) {
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Get all reservations for this order
            $stmt = $this->pdo->prepare("
                SELECT product_id, quantity FROM inventory_reservations
                WHERE order_id = ? AND status = 'reserved'
            ");
            $stmt->execute([$orderId]);
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $releasedItems = [];
            
            foreach ($reservations as $reservation) {
                $productId = $reservation['product_id'];
                $quantity = $reservation['quantity'];
                
                // Release stock
                $stmt = $this->pdo->prepare("
                    UPDATE inventory
                    SET available_stock = available_stock + ?,
                        reserved_stock = reserved_stock - ?,
                        last_updated = NOW()
                    WHERE product_id = ?
                ");
                $stmt->execute([$quantity, $quantity, $productId]);
                
                // Update reservation status
                $stmt = $this->pdo->prepare("
                    UPDATE inventory_reservations
                    SET status = 'released', updated_at = NOW()
                    WHERE order_id = ? AND product_id = ?
                ");
                $stmt->execute([$orderId, $productId]);
                
                // Log release
                $this->logger->logInventoryChange(
                    $productId,
                    $quantity,
                    (int)$reservation['quantity'],
                    (int)$reservation['quantity'] + $quantity,
                    'order_cancellation',
                    null,
                    $orderId
                );
                
                $releasedItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity
                ];
            }
            
            // Commit transaction
            $this->pdo->commit();
            
            return [
                'success' => true,
                'order_id' => $orderId,
                'released_items' => $releasedItems,
                'message' => 'Inventory released successfully'
            ];
            
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            $this->logger->logError('releaseInventory', $e->getMessage());
            return [
                'success' => false,
                'error' => 'Database error during release'
            ];
        }
    }
    
    /**
     * Check for low stock and create alerts
     * 
     * @return array Alert creation result
     */
    public function checkLowStockAlerts() {
        try {
            // Get all products with low stock
            $stmt = $this->pdo->prepare("
                SELECT id, product_id, available_stock, low_stock_threshold
                FROM inventory
                WHERE available_stock <= low_stock_threshold
                AND available_stock > 0
            ");
            $stmt->execute();
            $lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $alertsCreated = 0;
            
            foreach ($lowStockProducts as $product) {
                // Check if alert already exists for today
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) FROM inventory_alerts
                    WHERE product_id = ?
                    AND DATE(created_at) = CURDATE()
                    AND status = 'active'
                ");
                $stmt->execute([$product['product_id']]);
                $existingAlert = $stmt->fetchColumn();
                
                if ($existingAlert == 0) {
                    // Create new alert
                    $stmt = $this->pdo->prepare("
                        INSERT INTO inventory_alerts (product_id, current_stock, threshold, status, created_at)
                        VALUES (?, ?, ?, 'active', NOW())
                    ");
                    $stmt->execute([
                        $product['product_id'],
                        $product['available_stock'],
                        $product['low_stock_threshold']
                    ]);
                    
                    $alertsCreated++;
                    
                    // Log alert
                    $this->logger->logLowStockAlert(
                        $product['product_id'],
                        $product['available_stock'],
                        $product['low_stock_threshold']
                    );
                }
            }
            
            return [
                'success' => true,
                'alerts_created' => $alertsCreated,
                'low_stock_products' => count($lowStockProducts)
            ];
            
        } catch (\PDOException $e) {
            $this->logger->logError('checkLowStockAlerts', $e->getMessage());
            return [
                'success' => false,
                'error' => 'Database error'
            ];
        }
    }
    
    /**
     * Get inventory audit trail
     * 
     * @param int $productId Product ID
     * @param int $limit Number of records
     * @return array Audit trail
     */
    public function getAuditTrail($productId, $limit = 100) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM inventory_logs
                WHERE product_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            
            $stmt->bindValue(1, $productId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'product_id' => $productId,
                'logs' => $logs,
                'total' => count($logs)
            ];
            
        } catch (\PDOException $e) {
            $this->logger->logError('getAuditTrail', $e->getMessage());
            return [
                'success' => false,
                'error' => 'Database error'
            ];
        }
    }
}

/**
 * InventoryLogger - Log inventory changes and events
 */
class InventoryLogger {
    
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Log inventory change
     */
    public function logInventoryChange($productId, $quantityChange, $previousStock, $newStock, $reason, $userId = null, $orderId = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO inventory_logs (product_id, quantity_change, previous_stock, new_stock, reason, user_id, order_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $productId,
                $quantityChange,
                $previousStock,
                $newStock,
                $reason,
                $userId,
                $orderId
            ]);
            
        } catch (\PDOException $e) {
            error_log("Inventory logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Log low stock alert
     */
    public function logLowStockAlert($productId, $currentStock, $threshold) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO inventory_logs (product_id, reason, previous_stock, new_stock, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $productId,
                'low_stock_alert',
                $currentStock,
                $threshold
            ]);
            
        } catch (\PDOException $e) {
            error_log("Low stock alert logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Log error
     */
    public function logError($method, $error) {
        error_log("InventoryService::{$method} - {$error}");
    }
}
