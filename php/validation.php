<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Input Validation Module
 */

class Validation {
    
    /**
     * Validate email format
     */
    public static function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     * Minimum 8 characters, at least one uppercase, one lowercase, one number
     */
    public static function validate_password($password) {
        if (strlen($password) < 8) {
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        return true;
    }
    
    /**
     * Validate product data
     */
    public static function validate_product_data($data) {
        if (empty($data['name']) || strlen($data['name']) < 3) {
            return false;
        }
        if (empty($data['description']) || strlen($data['description']) < 10) {
            return false;
        }
        if (!isset($data['price']) || $data['price'] <= 0) {
            return false;
        }
        if (!isset($data['stock_quantity']) || $data['stock_quantity'] < 0) {
            return false;
        }
        if (empty($data['category_id']) || $data['category_id'] <= 0) {
            return false;
        }
        return true;
    }
    
    /**
     * Validate order data
     */
    public static function validate_order_data($data) {
        if (empty($data['shipping_address'])) {
            return false;
        }
        if (empty($data['payment_method'])) {
            return false;
        }
        if (!in_array($data['payment_method'], PAYMENT_METHODS)) {
            return false;
        }
        return true;
    }
    
    /**
     * Sanitize input to prevent SQL injection
     */
    public static function sanitize_input($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate file upload
     */
    public static function validate_file_upload($file) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            return false;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            return false;
        }
        return true;
    }
}
?>
