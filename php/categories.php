<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Category Management Module
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

class Categories {
    
    /**
     * Get all categories
     */
    public static function get_all_categories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        return Database::fetch_all($sql);
    }
    
    /**
     * Get category by ID
     */
    public static function get_category_by_id($category_id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        return Database::fetch_one($sql, [$category_id]);
    }
    
    /**
     * Add a new category
     */
    public static function add_category($name, $description = '') {
        // Validate inputs
        if (empty($name) || strlen($name) < 2) {
            return ['success' => false, 'message' => 'Category name must be at least 2 characters'];
        }
        
        // Check if category already exists
        $existing = Database::fetch_one("SELECT id FROM categories WHERE name = ?", [$name]);
        
        if ($existing) {
            return ['success' => false, 'message' => 'Category already exists'];
        }
        
        try {
            $stmt = Database::execute_query(
                "INSERT INTO categories (name, description) VALUES (?, ?)",
                [$name, $description]
            );
            
            $category_id = Database::last_insert_id();
            
            return ['success' => true, 'message' => 'Category added successfully', 'category_id' => $category_id];
        } catch (Exception $e) {
            error_log('Add category error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error adding category'];
        }
    }
    
    /**
     * Update category
     */
    public static function update_category($category_id, $name, $description = '') {
        // Validate inputs
        if (empty($name) || strlen($name) < 2) {
            return ['success' => false, 'message' => 'Category name must be at least 2 characters'];
        }
        
        // Check if another category with same name exists
        $existing = Database::fetch_one(
            "SELECT id FROM categories WHERE name = ? AND id != ?",
            [$name, $category_id]
        );
        
        if ($existing) {
            return ['success' => false, 'message' => 'Category name already exists'];
        }
        
        try {
            Database::execute_query(
                "UPDATE categories SET name = ?, description = ? WHERE id = ?",
                [$name, $description, $category_id]
            );
            
            return ['success' => true, 'message' => 'Category updated successfully'];
        } catch (Exception $e) {
            error_log('Update category error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating category'];
        }
    }
    
    /**
     * Delete category
     * Reassign products to default category
     */
    public static function delete_category($category_id) {
        try {
            // Get default category (first category)
            $default_category = Database::fetch_one(
                "SELECT id FROM categories WHERE id != ? ORDER BY id ASC LIMIT 1",
                [$category_id]
            );
            
            if ($default_category) {
                // Reassign products to default category
                Database::execute_query(
                    "UPDATE products SET category_id = ? WHERE category_id = ?",
                    [$default_category['id'], $category_id]
                );
            }
            
            // Delete category
            Database::execute_query("DELETE FROM categories WHERE id = ?", [$category_id]);
            
            return ['success' => true, 'message' => 'Category deleted successfully'];
        } catch (Exception $e) {
            error_log('Delete category error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting category'];
        }
    }
    
    /**
     * Get category count
     */
    public static function get_category_count() {
        $result = Database::fetch_one("SELECT COUNT(*) as count FROM categories");
        return $result['count'] ?? 0;
    }
}

// Helper functions for backward compatibility
function get_all_categories() {
    return Categories::get_all_categories();
}

function get_category_by_id($category_id) {
    return Categories::get_category_by_id($category_id);
}

function add_category($name, $description = '') {
    return Categories::add_category($name, $description);
}

function update_category($category_id, $name, $description = '') {
    return Categories::update_category($category_id, $name, $description);
}

function delete_category($category_id) {
    return Categories::delete_category($category_id);
}
?>
