<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Shopping Cart Management Module
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/products.php';

class Cart {
    const CART_SESSION_KEY = 'shopping_cart';
    
    /**
     * Add item to cart
     */
    public static function add_to_cart($product_id, $quantity = 1) {
        // Validate inputs
        if (!is_numeric($product_id) || $product_id <= 0) {
            return ['success' => false, 'message' => 'Invalid product ID'];
        }
        
        if (!is_numeric($quantity) || $quantity <= 0) {
            return ['success' => false, 'message' => 'Invalid quantity'];
        }
        
        // Get product details
        $product = Products::get_product_by_id($product_id);
        
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        // Check stock availability
        if ($product['stock_quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION[self::CART_SESSION_KEY])) {
            $_SESSION[self::CART_SESSION_KEY] = [];
        }
        
        // Add or update item in cart
        if (isset($_SESSION[self::CART_SESSION_KEY][$product_id])) {
            $new_quantity = $_SESSION[self::CART_SESSION_KEY][$product_id]['quantity'] + $quantity;
            
            // Check if new quantity exceeds stock
            if ($new_quantity > $product['stock_quantity']) {
                return ['success' => false, 'message' => 'Insufficient stock for requested quantity'];
            }
            
            $_SESSION[self::CART_SESSION_KEY][$product_id]['quantity'] = $new_quantity;
        } else {
            $_SESSION[self::CART_SESSION_KEY][$product_id] = [
                'product_id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image_path' => $product['image_path']
            ];
        }
        
        return ['success' => true, 'message' => 'Product added to cart'];
    }
    
    /**
     * Update cart item quantity
     */
    public static function update_cart_item($product_id, $quantity) {
        // Validate inputs
        if (!is_numeric($product_id) || $product_id <= 0) {
            return ['success' => false, 'message' => 'Invalid product ID'];
        }
        
        if (!is_numeric($quantity) || $quantity < 0) {
            return ['success' => false, 'message' => 'Invalid quantity'];
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION[self::CART_SESSION_KEY])) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }
        
        // Check if item exists in cart
        if (!isset($_SESSION[self::CART_SESSION_KEY][$product_id])) {
            return ['success' => false, 'message' => 'Item not in cart'];
        }
        
        if ($quantity == 0) {
            // Remove item if quantity is 0
            unset($_SESSION[self::CART_SESSION_KEY][$product_id]);
            return ['success' => true, 'message' => 'Item removed from cart'];
        }
        
        // Get product to check stock
        $product = Products::get_product_by_id($product_id);
        
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        // Check stock availability
        if ($quantity > $product['stock_quantity']) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }
        
        $_SESSION[self::CART_SESSION_KEY][$product_id]['quantity'] = $quantity;
        
        return ['success' => true, 'message' => 'Cart updated'];
    }
    
    /**
     * Remove item from cart
     */
    public static function remove_from_cart($product_id) {
        // Validate input
        if (!is_numeric($product_id) || $product_id <= 0) {
            return ['success' => false, 'message' => 'Invalid product ID'];
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION[self::CART_SESSION_KEY])) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }
        
        // Check if item exists in cart
        if (!isset($_SESSION[self::CART_SESSION_KEY][$product_id])) {
            return ['success' => false, 'message' => 'Item not in cart'];
        }
        
        unset($_SESSION[self::CART_SESSION_KEY][$product_id]);
        
        return ['success' => true, 'message' => 'Item removed from cart'];
    }
    
    /**
     * Get cart contents
     */
    public static function get_cart() {
        if (!isset($_SESSION[self::CART_SESSION_KEY])) {
            return [];
        }
        
        return $_SESSION[self::CART_SESSION_KEY];
    }
    
    /**
     * Clear cart
     */
    public static function clear_cart() {
        $_SESSION[self::CART_SESSION_KEY] = [];
        return ['success' => true, 'message' => 'Cart cleared'];
    }
    
    /**
     * Calculate cart total
     */
    public static function calculate_cart_total() {
        $cart = self::get_cart();
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }
    
    /**
     * Get cart item count
     */
    public static function get_cart_count() {
        $cart = self::get_cart();
        $count = 0;
        
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        
        return $count;
    }
    
    /**
     * Validate cart before checkout
     */
    public static function validate_cart() {
        $cart = self::get_cart();
        
        if (empty($cart)) {
            return ['valid' => false, 'message' => 'Cart is empty'];
        }
        
        // Verify all items still have sufficient stock
        foreach ($cart as $product_id => $item) {
            $product = Products::get_product_by_id($product_id);
            
            if (!$product) {
                return ['valid' => false, 'message' => 'Product no longer available: ' . $item['name']];
            }
            
            if ($product['stock_quantity'] < $item['quantity']) {
                return ['valid' => false, 'message' => 'Insufficient stock for: ' . $item['name']];
            }
        }
        
        return ['valid' => true, 'message' => 'Cart is valid'];
    }
}

// Helper functions for backward compatibility
function add_to_cart($product_id, $quantity = 1) {
    return Cart::add_to_cart($product_id, $quantity);
}

function update_cart_item($product_id, $quantity) {
    return Cart::update_cart_item($product_id, $quantity);
}

function remove_from_cart($product_id) {
    return Cart::remove_from_cart($product_id);
}

function get_cart() {
    return Cart::get_cart();
}

function clear_cart() {
    return Cart::clear_cart();
}

function calculate_cart_total() {
    return Cart::calculate_cart_total();
}

function get_cart_count() {
    return Cart::get_cart_count();
}

function validate_cart() {
    return Cart::validate_cart();
}
?>
