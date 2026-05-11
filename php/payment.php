<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Payment Processing Module
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/orders.php';
require_once __DIR__ . '/telebirr-token.php';

class Payment {
    
    /**
     * Process payment (simulated with API integration)
     */
    public static function process_payment($order_id, $payment_method, $credentials = []) {
        // Validate inputs
        if (!is_numeric($order_id) || $order_id <= 0) {
            return ['success' => false, 'message' => 'Invalid order ID'];
        }
        
        if (empty($payment_method) || !in_array($payment_method, PAYMENT_METHODS)) {
            return ['success' => false, 'message' => 'Invalid payment method'];
        }
        
        // Get order details
        $order = Orders::get_order_by_id($order_id);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        // Check if payment already exists
        $existing_payment = Database::fetch_one(
            "SELECT id FROM payments WHERE order_id = ?",
            [$order_id]
        );
        
        if ($existing_payment) {
            return ['success' => false, 'message' => 'Payment already processed for this order'];
        }
        
        try {
            // Process payment based on method
            $api_result = self::call_payment_api($payment_method, $order, $credentials);
            
            $payment_result = $api_result['success'];
            $status = $payment_result ? 'Completed' : 'Failed';
            $transaction_id = $api_result['transaction_id'] ?? self::generate_transaction_id($payment_method);
            
            // Create payment record
            $stmt = Database::execute_query(
                "INSERT INTO payments (order_id, payment_method, amount, status, transaction_id) 
                 VALUES (?, ?, ?, ?, ?)",
                [$order_id, $payment_method, $order['total_amount'], $status, $transaction_id]
            );
            
            // Update order status if payment successful
            if ($payment_result) {
                Orders::update_order_status($order_id, 'Paid');
            }
            
            return [
                'success' => $payment_result,
                'message' => $api_result['message'] ?? ($payment_result ? 'Payment successful' : 'Payment failed'),
                'transaction_id' => $transaction_id,
                'status' => $status
            ];
        } catch (Exception $e) {
            error_log('Process payment error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error processing payment'];
        }
    }
    
    /**
     * Call payment API based on payment method
     */
    private static function call_payment_api($payment_method, $order, $credentials) {
        switch ($payment_method) {
            case 'Telebirr':
                return self::process_telebirr_payment($order, $credentials);
            case 'CBE Birr':
                return self::process_cbe_payment($order, $credentials);
            case 'BOA':
                return self::process_boa_payment($order, $credentials);
            case 'Cash on Delivery':
                // Cash on Delivery is always successful (payment on delivery)
                return [
                    'success' => true,
                    'message' => 'Cash on Delivery order confirmed',
                    'transaction_id' => 'COD-' . time() . '-' . rand(1000, 9999)
                ];
            default:
                return ['success' => false, 'message' => 'Unknown payment method'];
        }
    }
    
    /**
     * Process Telebirr payment (Real API integration)
     */
    private static function process_telebirr_payment($order, $credentials) {
        $phone = $credentials['phone'] ?? '';
        $password = $credentials['password'] ?? '';
        
        // Validate credentials
        if (empty($phone) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Phone number and password are required',
                'transaction_id' => null
            ];
        }
        
        // Validate phone format
        if (!preg_match('/^\+?251[0-9]{9}$/', $phone)) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format. Use +251 followed by 9 digits',
                'transaction_id' => null
            ];
        }
        
        // Check if real API credentials are configured
        if (defined('TELEBIRR_MERCHANT_ID') && defined('TELEBIRR_API_KEY')) {
            // Use real Telebirr API
            return self::call_real_telebirr_api($phone, $password, $order);
        } else {
            // Fallback to simulation for testing
            $success = rand(1, 100) <= (PAYMENT_SUCCESS_RATE * 100);
            error_log("Telebirr Simulated: Phone={$phone}, Amount={$order['total_amount']}, Status=" . ($success ? 'Success' : 'Failed'));
            
            return [
                'success' => $success,
                'message' => $success ? 'Telebirr payment confirmed' : 'Telebirr payment failed',
                'transaction_id' => 'TBR-' . time() . '-' . rand(1000, 9999)
            ];
        }
    }
    
    /**
     * Get Telebirr Authentication Token
     * 
     * This method uses the ApplyFabricToken class to generate
     * an authentication token from Telebirr's API
     */
    private static function get_telebirr_token() {
        try {
            // Get credentials from config.php
            $fabricAppId = defined('TELEBIRR_FABRIC_APP_ID') ? TELEBIRR_FABRIC_APP_ID : '';
            $appSecret = defined('TELEBIRR_API_KEY') ? TELEBIRR_API_KEY : '';
            $merchantAppId = defined('TELEBIRR_MERCHANT_ID') ? TELEBIRR_MERCHANT_ID : '';
            $baseUrl = defined('TELEBIRR_API_URL') ? TELEBIRR_API_URL : 'https://sandbox-api.telebirr.et';
            
            // Validate that we have all required credentials
            if (empty($fabricAppId) || empty($appSecret) || empty($merchantAppId)) {
                error_log("Telebirr Token Error: Missing credentials in config.php");
                return null;
            }
            
            // Create token generator instance
            $tokenGenerator = new ApplyFabricToken($baseUrl, $fabricAppId, $appSecret, $merchantAppId);
            
            // Generate and return the token
            $token = $tokenGenerator->applyFabricToken();
            
            if (empty($token)) {
                error_log("Telebirr Token Error: Failed to generate token");
                return null;
            }
            
            error_log("Telebirr Token Generated Successfully");
            return $token;
            
        } catch (Exception $e) {
            error_log("Telebirr Token Exception: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Call real Telebirr API
     */
    private static function call_real_telebirr_api($phone, $password, $order) {
        try {
            $merchant_id = defined('TELEBIRR_MERCHANT_ID') ? TELEBIRR_MERCHANT_ID : '';
            $api_key = defined('TELEBIRR_API_KEY') ? TELEBIRR_API_KEY : '';
            $api_url = defined('TELEBIRR_API_URL') ? TELEBIRR_API_URL : 'https://api.telebirr.et/v1';
            
            // Prepare payment request
            $payload = [
                'merchant_id' => $merchant_id,
                'phone_number' => $phone,
                'amount' => $order['total_amount'],
                'currency' => 'ETB',
                'order_id' => $order['id'],
                'description' => 'DEBRE TABOR GEBEYA Order #' . $order['id'],
                'callback_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/customer/payment-result.php'
            ];
            
            // Create signature
            $signature = hash_hmac('sha256', json_encode($payload), $api_key);
            
            // Make API request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $api_url . '/payment/initiate',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $api_key,
                    'X-Signature: ' . $signature
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Parse response
            $result = json_decode($response, true);
            
            if ($http_code === 200 && isset($result['success']) && $result['success']) {
                error_log("Telebirr Real API Success: Phone={$phone}, Amount={$order['total_amount']}, TxnID={$result['transaction_id']}");
                
                return [
                    'success' => true,
                    'message' => 'Telebirr payment initiated successfully',
                    'transaction_id' => $result['transaction_id'] ?? 'TBR-' . time()
                ];
            } else {
                $error_msg = $result['message'] ?? 'Payment processing failed';
                error_log("Telebirr Real API Failed: Phone={$phone}, Error={$error_msg}");
                
                return [
                    'success' => false,
                    'message' => $error_msg,
                    'transaction_id' => null
                ];
            }
        } catch (Exception $e) {
            error_log("Telebirr API Exception: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error connecting to payment gateway: ' . $e->getMessage(),
                'transaction_id' => null
            ];
        }
    }
    
    /**
     * Process CBE payment (simulated API call)
     */
    private static function process_cbe_payment($order, $credentials) {
        // Simulate CBE API call
        // In production, this would call the actual CBE API
        
        $account = $credentials['account_number'] ?? '';
        $pin = $credentials['pin'] ?? '';
        
        // Simulate API validation
        if (empty($account) || empty($pin)) {
            return [
                'success' => false,
                'message' => 'Invalid CBE credentials',
                'transaction_id' => null
            ];
        }
        
        // Simulate API response (70% success rate)
        $success = rand(1, 100) <= (PAYMENT_SUCCESS_RATE * 100);
        
        // Log the simulated API call
        error_log("CBE API Call: Account={$account}, Amount={$order['total_amount']}, Status=" . ($success ? 'Success' : 'Failed'));
        
        return [
            'success' => $success,
            'message' => $success ? 'CBE payment confirmed' : 'CBE payment failed',
            'transaction_id' => 'CBE-' . time() . '-' . rand(1000, 9999)
        ];
    }
    
    /**
     * Process BOA payment (simulated API call)
     */
    private static function process_boa_payment($order, $credentials) {
        // Simulate BOA API call
        // In production, this would call the actual BOA API
        
        $account = $credentials['account_number'] ?? '';
        $pin = $credentials['pin'] ?? '';
        
        // Simulate API validation
        if (empty($account) || empty($pin)) {
            return [
                'success' => false,
                'message' => 'Invalid BOA credentials',
                'transaction_id' => null
            ];
        }
        
        // Simulate API response (70% success rate)
        $success = rand(1, 100) <= (PAYMENT_SUCCESS_RATE * 100);
        
        // Log the simulated API call
        error_log("BOA API Call: Account={$account}, Amount={$order['total_amount']}, Status=" . ($success ? 'Success' : 'Failed'));
        
        return [
            'success' => $success,
            'message' => $success ? 'BOA payment confirmed' : 'BOA payment failed',
            'transaction_id' => 'BOA-' . time() . '-' . rand(1000, 9999)
        ];
    }
    
    /**
     * Simulate payment result (70% success rate)
     */
    public static function simulate_payment_result() {
        $random = rand(1, 100);
        return $random <= (PAYMENT_SUCCESS_RATE * 100);
    }
    
    /**
     * Generate transaction ID
     */
    private static function generate_transaction_id($payment_method) {
        $prefix = substr($payment_method, 0, 3);
        $timestamp = time();
        $random = rand(1000, 9999);
        return strtoupper($prefix) . '-' . $timestamp . '-' . $random;
    }
    
    /**
     * Update payment status
     */
    public static function update_payment_status($order_id, $status) {
        // Validate status
        $valid_statuses = ['Pending', 'Completed', 'Failed'];
        
        if (!in_array($status, $valid_statuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        try {
            Database::execute_query(
                "UPDATE payments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE order_id = ?",
                [$status, $order_id]
            );
            
            return ['success' => true, 'message' => 'Payment status updated'];
        } catch (Exception $e) {
            error_log('Update payment status error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating payment status'];
        }
    }
    
    /**
     * Get payment by order ID
     */
    public static function get_payment_by_order_id($order_id) {
        $sql = "SELECT * FROM payments WHERE order_id = ?";
        return Database::fetch_one($sql, [$order_id]);
    }
    
    /**
     * Get all payments
     */
    public static function get_all_payments() {
        $sql = "SELECT p.*, o.customer_id FROM payments p 
                LEFT JOIN orders o ON p.order_id = o.id 
                ORDER BY p.created_at DESC";
        return Database::fetch_all($sql);
    }
    
    /**
     * Get payment count
     */
    public static function get_payment_count() {
        $result = Database::fetch_one("SELECT COUNT(*) as count FROM payments");
        return $result['count'] ?? 0;
    }
    
    /**
     * Get successful payment count
     */
    public static function get_successful_payment_count() {
        $result = Database::fetch_one(
            "SELECT COUNT(*) as count FROM payments WHERE status = 'Completed'"
        );
        return $result['count'] ?? 0;
    }
    
    /**
     * Get total payment amount
     */
    public static function get_total_payment_amount() {
        $result = Database::fetch_one(
            "SELECT SUM(amount) as total FROM payments WHERE status = 'Completed'"
        );
        return $result['total'] ?? 0;
    }
}

// Helper functions for backward compatibility
function process_payment($order_id, $payment_method, $credentials = []) {
    return Payment::process_payment($order_id, $payment_method, $credentials);
}

function simulate_payment_result() {
    return Payment::simulate_payment_result();
}

function update_payment_status($order_id, $status) {
    return Payment::update_payment_status($order_id, $status);
}

function get_payment_by_order_id($order_id) {
    return Payment::get_payment_by_order_id($order_id);
}

function get_all_payments() {
    return Payment::get_all_payments();
}
?>
