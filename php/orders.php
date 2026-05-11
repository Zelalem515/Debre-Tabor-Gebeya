<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Order Management Module
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cart.php';

class Orders {
    
    /**
     * Create a new order
     */
    public static function create_order($customer_id, $items, $shipping_address, $payment_method) {
        // Validate inputs
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return ['success' => false, 'message' => 'Invalid customer ID'];
        }
        
        if (empty($items) || !is_array($items)) {
            return ['success' => false, 'message' => 'No items in order'];
        }
        
        if (empty($shipping_address)) {
            return ['success' => false, 'message' => 'Shipping address required'];
        }
        
        if (empty($payment_method)) {
            return ['success' => false, 'message' => 'Payment method required'];
        }
        
        // Calculate total amount
        $total_amount = 0;
        foreach ($items as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }
        
        try {
            // Start transaction
            $connection = Database::get_connection();
            $connection->beginTransaction();
            
            // Create order
            $stmt = Database::execute_query(
                "INSERT INTO orders (customer_id, total_amount, status, shipping_address, payment_method) 
                 VALUES (?, ?, 'Pending', ?, ?)",
                [$customer_id, $total_amount, $shipping_address, $payment_method]
            );
            
            $order_id = Database::last_insert_id();
            
            if (!$order_id) {
                throw new Exception('Failed to create order - no ID returned');
            }
            
            // Add order items
            foreach ($items as $item) {
                $product_id = $item['product_id'];
                $seller_id = isset($item['seller_id']) ? $item['seller_id'] : null;
                $quantity = isset($item['quantity']) ? $item['quantity'] : 1;
                $price = isset($item['price']) ? $item['price'] : 0;
                
                // If seller_id not provided, get it from the product
                if (!$seller_id) {
                    $product = Database::fetch_one("SELECT seller_id FROM products WHERE id = ?", [$product_id]);
                    $seller_id = $product ? $product['seller_id'] : null;
                }
                
                Database::execute_query(
                    "INSERT INTO order_items (order_id, product_id, seller_id, quantity, unit_price, subtotal) 
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [$order_id, $product_id, $seller_id, $quantity, $price, $price * $quantity]
                );
                
                // Reduce product stock
                Database::execute_query(
                    "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?",
                    [$quantity, $product_id]
                );
            }
            
            // Commit transaction
            $connection->commit();
            
            return ['success' => true, 'message' => 'Order created successfully', 'order_id' => $order_id];
        } catch (Exception $e) {
            // Rollback transaction if it was started
            try {
                $connection->rollBack();
            } catch (Exception $rollback_error) {
                error_log('Rollback error: ' . $rollback_error->getMessage());
            }
            error_log('Create order error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating order: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get customer orders
     */
    public static function get_customer_orders($customer_id) {
        $sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC";
        return Database::fetch_all($sql, [$customer_id]);
    }
    
    /**
     * Get seller orders (orders containing seller's products)
     */
    public static function get_seller_orders($seller_id) {
        $sql = "SELECT DISTINCT o.* FROM orders o 
                INNER JOIN order_items oi ON o.id = oi.order_id 
                WHERE oi.seller_id = ? 
                ORDER BY o.created_at DESC";
        return Database::fetch_all($sql, [$seller_id]);
    }
    
    /**
     * Get all orders (admin)
     */
    public static function get_all_orders() {
        $sql = "SELECT o.*, u.full_name as customer_name FROM orders o 
                LEFT JOIN users u ON o.customer_id = u.id 
                ORDER BY o.created_at DESC";
        return Database::fetch_all($sql);
    }
    
    /**
     * Get order by ID
     */
    public static function get_order_by_id($order_id) {
        $sql = "SELECT o.*, u.full_name as customer_name, u.email as customer_email 
                FROM orders o 
                LEFT JOIN users u ON o.customer_id = u.id 
                WHERE o.id = ?";
        return Database::fetch_one($sql, [$order_id]);
    }
    
    /**
     * Get order items
     */
    public static function get_order_items($order_id) {
        $sql = "SELECT oi.*, p.name as product_name, u.full_name as seller_name 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                LEFT JOIN users u ON oi.seller_id = u.id 
                WHERE oi.order_id = ?";
        return Database::fetch_all($sql, [$order_id]);
    }
    
    /**
     * Update order status
     */
    public static function update_order_status($order_id, $status) {
        // Validate status
        $valid_statuses = ['Pending', 'Paid', 'Shipped', 'Delivered'];
        
        if (!in_array($status, $valid_statuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        try {
            Database::execute_query(
                "UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$status, $order_id]
            );
            
            return ['success' => true, 'message' => 'Order status updated'];
        } catch (Exception $e) {
            error_log('Update order status error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating order status'];
        }
    }
    
    /**
     * Get order count
     */
    public static function get_order_count() {
        $result = Database::fetch_one("SELECT COUNT(*) as count FROM orders");
        return $result['count'] ?? 0;
    }
    
    /**
     * Get total revenue
     */
    public static function get_total_revenue() {
        $result = Database::fetch_one(
            "SELECT SUM(total_amount) as total FROM orders WHERE status = 'Delivered'"
        );
        return $result['total'] ?? 0;
    }
    
    /**
     * Get customer order count
     */
    public static function get_customer_order_count($customer_id) {
        $result = Database::fetch_one(
            "SELECT COUNT(*) as count FROM orders WHERE customer_id = ?",
            [$customer_id]
        );
        return $result['count'] ?? 0;
    }
    
    /**
     * Get customer total spent
     */
    public static function get_customer_total_spent($customer_id) {
        $result = Database::fetch_one(
            "SELECT SUM(total_amount) as total FROM orders WHERE customer_id = ? AND status = 'Delivered'",
            [$customer_id]
        );
        return $result['total'] ?? 0;
    }
}

// Helper functions for backward compatibility
function create_order($customer_id, $items, $shipping_address, $payment_method) {
    return Orders::create_order($customer_id, $items, $shipping_address, $payment_method);
}

function get_customer_orders($customer_id) {
    return Orders::get_customer_orders($customer_id);
}

function get_seller_orders($seller_id) {
    return Orders::get_seller_orders($seller_id);
}

function get_all_orders() {
    return Orders::get_all_orders();
}

function get_order_by_id($order_id) {
    return Orders::get_order_by_id($order_id);
}

function get_order_items($order_id) {
    return Orders::get_order_items($order_id);
}

function update_order_status($order_id, $status) {
    return Orders::update_order_status($order_id, $status);
}
?>
