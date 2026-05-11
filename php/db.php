<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Database Connection Module
 * 
 * Handles all database operations using PDO prepared statements
 */

require_once __DIR__ . '/../config.php';

class Database {
    private static $connection = null;
    
    /**
     * Get database connection (singleton pattern)
     * 
     * @return PDO Database connection
     * @throws Exception If connection fails
     */
    public static function get_connection() {
        if (self::$connection === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                self::$connection = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                throw new Exception('Database connection failed. Please try again later.');
            }
        }
        return self::$connection;
    }
    
    /**
     * Execute a prepared statement query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return PDOStatement Executed statement
     * @throws Exception If query execution fails
     */
    public static function execute_query($sql, $params = []) {
        try {
            $connection = self::get_connection();
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query execution failed: ' . $e->getMessage());
            throw new Exception('Database query failed. Please try again later.');
        }
    }
    
    /**
     * Fetch all results from a query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array Array of results
     */
    public static function fetch_all($sql, $params = []) {
        try {
            $stmt = self::execute_query($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Fetch all failed: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Fetch a single result from a query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind to the query
     * @return array|null Single result or null if not found
     */
    public static function fetch_one($sql, $params = []) {
        try {
            $stmt = self::execute_query($sql, $params);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Fetch one failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return string Last inserted ID
     */
    public static function last_insert_id() {
        try {
            $connection = self::get_connection();
            return $connection->lastInsertId();
        } catch (Exception $e) {
            error_log('Get last insert ID failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get the number of affected rows from last query
     * 
     * @param PDOStatement $stmt The statement to check
     * @return int Number of affected rows
     */
    public static function affected_rows($stmt) {
        try {
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log('Get affected rows failed: ' . $e->getMessage());
            return 0;
        }
    }
}

// Helper functions for backward compatibility
function get_db_connection() {
    return Database::get_connection();
}

function execute_query($sql, $params = []) {
    return Database::execute_query($sql, $params);
}

function fetch_all($sql, $params = []) {
    return Database::fetch_all($sql, $params);
}

function fetch_one($sql, $params = []) {
    return Database::fetch_one($sql, $params);
}
?>
