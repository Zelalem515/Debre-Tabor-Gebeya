<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Product Management Module
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/validation.php';

class Products {
    
    /**
     * Get all products
     */
    public static function get_all_products($limit = null, $offset = 0) {
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return Database::fetch_all($sql, [$limit, $offset]);
        }
        
        return Database::fetch_all($sql);
    }
    
    /**
     * Get products by category
     */
    public static function get_products_by_category($category_id, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return Database::fetch_all($sql, [$category_id, $limit, $offset]);
        }
        
        return Database::fetch_all($sql, [$category_id]);
    }
    
    /**
     * Search products by name or description
     */
    public static function search_products($search_term, $limit = null, $offset = 0) {
        $search_term = '%' . $search_term . '%';
        $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return Database::fetch_all($sql, [$search_term, $search_term, $limit, $offset]);
        }
        
        return Database::fetch_all($sql, [$search_term, $search_term]);
    }
    
    /**
     * Get product by ID
     */
    public static function get_product_by_id($product_id) {
        $sql = "SELECT p.*, u.full_name as seller_name, u.email as seller_email, c.name as category_name 
                FROM products p 
                LEFT JOIN users u ON p.seller_id = u.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        
        return Database::fetch_one($sql, [$product_id]);
    }
    
    /**
     * Add a new product
     */
    public static function add_product($seller_id, $name, $description, $price, $category_id, $stock, $image_path = null) {
        // Validate inputs
        if (empty($name) || strlen($name) < 3) {
            return ['success' => false, 'message' => 'Product name must be at least 3 characters'];
        }
        
        if (empty($description) || strlen($description) < 10) {
            return ['success' => false, 'message' => 'Product description must be at least 10 characters'];
        }
        
        if (!is_numeric($price) || $price <= 0) {
            return ['success' => false, 'message' => 'Product price must be greater than 0'];
        }
        
        if (!is_numeric($stock) || $stock < 0) {
            return ['success' => false, 'message' => 'Product stock cannot be negative'];
        }
        
        if (!is_numeric($category_id) || $category_id <= 0) {
            return ['success' => false, 'message' => 'Invalid category'];
        }
        
        try {
            $stmt = Database::execute_query(
                "INSERT INTO products (seller_id, category_id, name, description, price, stock_quantity, image_url) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$seller_id, $category_id, $name, $description, $price, $stock, $image_path]
            );
            
            $product_id = Database::last_insert_id();
            
            return ['success' => true, 'message' => 'Product added successfully', 'product_id' => $product_id];
        } catch (Exception $e) {
            error_log('Add product error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error adding product'];
        }
    }
    
    /**
     * Update product
     */
    public static function update_product($product_id, $seller_id, $data) {
        // Verify seller owns this product
        $product = Database::fetch_one("SELECT seller_id FROM products WHERE id = ?", [$product_id]);
        
        if (!$product || $product['seller_id'] != $seller_id) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        // Build update query
        $updates = [];
        $params = [];
        
        if (isset($data['name']) && !empty($data['name'])) {
            $updates[] = "name = ?";
            $params[] = $data['name'];
        }
        
        if (isset($data['description']) && !empty($data['description'])) {
            $updates[] = "description = ?";
            $params[] = $data['description'];
        }
        
        if (isset($data['price']) && is_numeric($data['price']) && $data['price'] > 0) {
            $updates[] = "price = ?";
            $params[] = $data['price'];
        }
        
        if (isset($data['stock_quantity']) && is_numeric($data['stock_quantity']) && $data['stock_quantity'] >= 0) {
            $updates[] = "stock_quantity = ?";
            $params[] = $data['stock_quantity'];
        }
        
        if (isset($data['category_id']) && is_numeric($data['category_id']) && $data['category_id'] > 0) {
            $updates[] = "category_id = ?";
            $params[] = $data['category_id'];
        }
        
        if (isset($data['image_path']) && !empty($data['image_path'])) {
            $updates[] = "image_path = ?";
            $params[] = $data['image_path'];
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $product_id;
        
        try {
            $sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = ?";
            Database::execute_query($sql, $params);
            
            return ['success' => true, 'message' => 'Product updated successfully'];
        } catch (Exception $e) {
            error_log('Update product error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating product'];
        }
    }
    
    /**
     * Delete product
     */
    public static function delete_product($product_id, $seller_id) {
        // Verify seller owns this product
        $product = Database::fetch_one("SELECT seller_id FROM products WHERE id = ?", [$product_id]);
        
        if (!$product || $product['seller_id'] != $seller_id) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        try {
            Database::execute_query("DELETE FROM products WHERE id = ?", [$product_id]);
            
            return ['success' => true, 'message' => 'Product deleted successfully'];
        } catch (Exception $e) {
            error_log('Delete product error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting product'];
        }
    }
    
    /**
     * Get products for a specific seller
     */
    public static function get_seller_products($seller_id, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return Database::fetch_all($sql, [$seller_id, $limit, $offset]);
        }
        
        return Database::fetch_all($sql, [$seller_id]);
    }
    
    /**
     * Get product count
     */
    public static function get_product_count() {
        $result = Database::fetch_one("SELECT COUNT(*) as count FROM products");
        return $result['count'] ?? 0;
    }
    
    /**
     * Get seller product count
     */
    public static function get_seller_product_count($seller_id) {
        $result = Database::fetch_one("SELECT COUNT(*) as count FROM products WHERE seller_id = ?", [$seller_id]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Add product image
     */
    public static function add_product_image($product_id, $image_path, $image_order = 0, $is_primary = false) {
        try {
            Database::execute_query(
                "INSERT INTO product_images (product_id, image_path, image_order, is_primary) 
                 VALUES (?, ?, ?, ?)",
                [$product_id, $image_path, $image_order, $is_primary ? 1 : 0]
            );
            
            return ['success' => true, 'message' => 'Image added successfully'];
        } catch (Exception $e) {
            error_log('Add product image error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error adding image'];
        }
    }
    
    /**
     * Get product images
     */
    public static function get_product_images($product_id) {
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY image_order ASC";
        return Database::fetch_all($sql, [$product_id]);
    }
    
    /**
     * Get primary product image
     */
    public static function get_primary_product_image($product_id) {
        $sql = "SELECT * FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1";
        return Database::fetch_one($sql, [$product_id]);
    }
}

// Helper functions for backward compatibility
function get_all_products($limit = null, $offset = 0) {
    return Products::get_all_products($limit, $offset);
}

function get_products_by_category($category_id, $limit = null, $offset = 0) {
    return Products::get_products_by_category($category_id, $limit, $offset);
}

function search_products($search_term, $limit = null, $offset = 0) {
    return Products::search_products($search_term, $limit, $offset);
}

function get_product_by_id($product_id) {
    return Products::get_product_by_id($product_id);
}

function add_product($seller_id, $name, $description, $price, $category_id, $stock, $image_path = null) {
    return Products::add_product($seller_id, $name, $description, $price, $category_id, $stock, $image_path);
}

function update_product($product_id, $seller_id, $data) {
    return Products::update_product($product_id, $seller_id, $data);
}

function delete_product($product_id, $seller_id) {
    return Products::delete_product($product_id, $seller_id);
}

function get_seller_products($seller_id, $limit = null, $offset = 0) {
    return Products::get_seller_products($seller_id, $limit, $offset);
}

// Add these methods to the Products class before the closing brace
// Note: These should be added inside the class definition above
