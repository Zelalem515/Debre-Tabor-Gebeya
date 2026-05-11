<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Telebirr Payment Gateway Integration
 * 
 * This page redirects customers to Telebirr for payment confirmation
 * Telebirr will ask for password confirmation and process the payment
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/payment.php';
require_once __DIR__ . '/../php/telebirr-token.php';

session_start();

// Check if order ID is provided
$order_id = $_GET['order_id'] ?? $_POST['order_id'] ?? null;
$amount = $_GET['amount'] ?? $_POST['amount'] ?? null;

if (!$order_id) {
    die('Error: Order ID is required');
}

// Try to get order from database, or use test parameters
$order = Orders::get_order_by_id($order_id);

// If order not found in database, create a test order object
if (!$order) {
    if (!$amount) {
        die('Error: Order not found and amount not provided');
    }
    
    // Create test order object
    $order = [
        'id' => $order_id,
        'total_amount' => $amount,
        'customer_id' => 1,
        'status' => 'Pending'
    ];
    
    error_log("Using test order: ID=" . $order_id . ", Amount=" . $amount);
}

// Check if payment already exists (only for real orders in database)
if ($order['id'] > 0 && $order['id'] != 123) {
    $existing_payment = Database::fetch_one(
        "SELECT id FROM payments WHERE order_id = ?",
        [$order_id]
    );
    
    if ($existing_payment) {
        die('Error: Payment already processed for this order');
    }
}

// Get Telebirr credentials
$merchant_id = defined('TELEBIRR_MERCHANT_ID') ? TELEBIRR_MERCHANT_ID : '';
$fabric_app_id = defined('TELEBIRR_FABRIC_APP_ID') ? TELEBIRR_FABRIC_APP_ID : '';
$app_secret = defined('TELEBIRR_API_KEY') ? TELEBIRR_API_KEY : '';
$api_url = defined('TELEBIRR_API_URL') ? TELEBIRR_API_URL : 'https://sandbox-api.telebirr.et';

if (!$merchant_id || !$fabric_app_id || !$app_secret) {
    die('Error: Telebirr credentials not configured');
}

// Generate authentication token
try {
    $tokenGenerator = new ApplyFabricToken($api_url, $fabric_app_id, $app_secret, $merchant_id);
    $token = $tokenGenerator->applyFabricToken();
    
    if (!$token) {
        throw new Exception('Failed to generate Telebirr token');
    }
} catch (Exception $e) {
    error_log("Telebirr Token Error: " . $e->getMessage());
    die('Error: Failed to connect to Telebirr. ' . $e->getMessage());
}

// Prepare payment request
$callback_url = 'https://' . $_SERVER['HTTP_HOST'] . '/customer/telebirr-payment-callback.php';
$return_url = 'https://' . $_SERVER['HTTP_HOST'] . '/customer/payment-result.php?order_id=' . $order_id;

$payload = [
    'outTradeNo' => 'ORDER-' . $order_id . '-' . time(),
    'subject' => 'DEBRE TABOR GEBEYA Order #' . $order_id,
    'description' => 'Payment for order #' . $order_id,
    'totalAmount' => (int)($order['total_amount'] * 100), // Convert to cents
    'currency' => 'ETB',
    'timeout' => 1800, // 30 minutes
    'returnUrl' => $return_url,
    'notifyUrl' => $callback_url,
    'merchantId' => $merchant_id
];

// Create signature
$signature = hash_hmac('sha256', json_encode($payload), $app_secret);

// Log the payment initiation
error_log("=== TELEBIRR PAYMENT GATEWAY ===");
error_log("Order ID: " . $order_id);
error_log("Amount: " . $order['total_amount'] . " ETB");
error_log("Merchant ID: " . $merchant_id);
error_log("Callback URL: " . $callback_url);
error_log("=== END LOG ===");

// Make API request to initiate payment
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $api_url . '/api/v1/payment/initiate',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
        'X-Signature: ' . $signature
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false // For sandbox testing
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

error_log("Telebirr API Response Code: " . $http_code);
error_log("Telebirr API Response: " . $response);

// Parse response
$result = json_decode($response, true);

if ($http_code === 200 && isset($result['data']['paymentUrl'])) {
    // Store pending payment record (only for real orders)
    if ($order['id'] != 123) {
        Database::execute_query(
            "INSERT INTO payments (order_id, payment_method, amount, status, transaction_id) 
             VALUES (?, ?, ?, ?, ?)",
            [$order_id, 'Telebirr', $order['total_amount'], 'Pending', $result['data']['transactionId'] ?? 'TBR-' . time()]
        );
    }
    
    // Redirect to Telebirr payment page
    // This is where Telebirr will ask for password confirmation
    header('Location: ' . $result['data']['paymentUrl']);
    exit;
} else {
    $error_msg = $result['message'] ?? 'Failed to initiate payment';
    error_log("Telebirr Payment Initiation Failed: " . $error_msg);
    die('Error: ' . $error_msg . ' (HTTP ' . $http_code . ')');
}
?>
