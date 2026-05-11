<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Authentication Module
 * 
 * Handles user registration, login, logout, and session management
 */

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/validation.php';

class Auth {
    
    /**
     * Register a new user
     * 
     * @param string $email User email address
     * @param string $password User password (plaintext)
     * @param string $name User full name
     * @param string $role User role (customer, seller, admin)
     * @param string|null $profile_picture Profile picture path (optional, for sellers)
     * @return array Result array with 'success' and 'message' keys
     */
    public static function register_user($email, $password, $name, $role = 'customer', $profile_picture = null) {
        // Validate inputs
        if (!Validation::validate_email($email)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        if (!Validation::validate_password($password)) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, and numbers'];
        }
        
        if (empty($name) || strlen($name) < 2) {
            return ['success' => false, 'message' => 'Name must be at least 2 characters'];
        }
        
        if (!in_array($role, ['customer', 'seller', 'admin'])) {
            return ['success' => false, 'message' => 'Invalid role'];
        }
        
        // Check if email already exists
        $existing_user = Database::fetch_one(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if ($existing_user) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Hash password
        $password_hash = self::hash_password($password);
        
        // Insert user into database
        try {
            if ($profile_picture) {
                $stmt = Database::execute_query(
                    "INSERT INTO users (email, password_hash, full_name, role, profile_picture, is_active, is_verified, created_at) 
                     VALUES (?, ?, ?, ?, ?, TRUE, FALSE, CURRENT_TIMESTAMP)",
                    [$email, $password_hash, $name, $role, $profile_picture]
                );
            } else {
                $stmt = Database::execute_query(
                    "INSERT INTO users (email, password_hash, full_name, role, is_active, is_verified, created_at) 
                     VALUES (?, ?, ?, ?, TRUE, FALSE, CURRENT_TIMESTAMP)",
                    [$email, $password_hash, $name, $role]
                );
            }
            
            return ['success' => true, 'message' => 'Registration successful'];
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Login a user
     * 
     * @param string $email User email address
     * @param string $password User password (plaintext)
     * @return array Result array with 'success' and 'message' keys
     */
    public static function login_user($email, $password) {
        // Validate inputs
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }
        
        // Find user by email
        $user = Database::fetch_one(
            "SELECT id, email, password_hash, full_name, role, is_active FROM users WHERE email = ?",
            [$email]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is deactivated'];
        }
        
        // Verify password
        if (!self::verify_password($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        return ['success' => true, 'message' => 'Login successful'];
    }
    
    /**
     * Logout a user
     * 
     * @return void
     */
    public static function logout_user() {
        // Destroy session
        $_SESSION = [];
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if user is logged in, false otherwise
     */
    public static function is_logged_in() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user's role
     * 
     * @return string|null User role or null if not logged in
     */
    public static function get_user_role() {
        return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
    }
    
    /**
     * Get current user's ID
     * 
     * @return int|null User ID or null if not logged in
     */
    public static function get_user_id() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Get current user's email
     * 
     * @return string|null User email or null if not logged in
     */
    public static function get_user_email() {
        return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
    }
    
    /**
     * Get current user's name
     * 
     * @return string|null User name or null if not logged in
     */
    public static function get_user_name() {
        return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
    }
    
    /**
     * Hash a password using bcrypt
     * 
     * @param string $password Plaintext password
     * @return string Hashed password
     */
    public static function hash_password($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }
    
    /**
     * Verify a password against a hash
     * 
     * @param string $password Plaintext password
     * @param string $hash Password hash
     * @return bool True if password matches hash, false otherwise
     */
    public static function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Check session timeout
     * 
     * @return bool True if session is valid, false if expired
     */
    public static function check_session_timeout() {
        if (!self::is_logged_in()) {
            return false;
        }
        
        $login_time = $_SESSION['login_time'] ?? 0;
        $current_time = time();
        $elapsed_time = $current_time - $login_time;
        
        if ($elapsed_time > SESSION_TIMEOUT) {
            self::logout_user();
            return false;
        }
        
        // Update login time to extend session
        $_SESSION['login_time'] = $current_time;
        return true;
    }
    
    /**
     * Require login - redirect to login page if not logged in
     * 
     * @return void
     */
    public static function require_login() {
        if (!self::is_logged_in() || !self::check_session_timeout()) {
            header('Location: ' . SITE_URL . 'login.php');
            exit;
        }
    }
    
    /**
     * Require specific role - redirect if user doesn't have required role
     * 
     * @param string|array $required_role Required role(s)
     * @return void
     */
    public static function require_role($required_role) {
        self::require_login();
        
        $user_role = self::get_user_role();
        $required_roles = is_array($required_role) ? $required_role : [$required_role];
        
        if (!in_array($user_role, $required_roles)) {
            header('Location: ' . SITE_URL . 'index.php');
            exit;
        }
    }
}

// Helper functions for backward compatibility
function register_user($email, $password, $name, $role = 'customer', $profile_picture = null) {
    return Auth::register_user($email, $password, $name, $role, $profile_picture);
}

function login_user($email, $password) {
    return Auth::login_user($email, $password);
}

function logout_user() {
    return Auth::logout_user();
}

function is_logged_in() {
    return Auth::is_logged_in();
}

function get_user_role() {
    return Auth::get_user_role();
}

function get_user_id() {
    return Auth::get_user_id();
}

function get_user_email() {
    return Auth::get_user_email();
}

function get_user_name() {
    return Auth::get_user_name();
}

function hash_password($password) {
    return Auth::hash_password($password);
}

function verify_password($password, $hash) {
    return Auth::verify_password($password, $hash);
}

function check_session_timeout() {
    return Auth::check_session_timeout();
}

function require_login() {
    return Auth::require_login();
}

function require_role($required_role) {
    return Auth::require_role($required_role);
}
?>
