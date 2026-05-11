<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Telebirr Payment Callback Handler
 * 
 * This page receives payment confirmation from Telebirr
 * and updates the order status
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/payment.php';

// Get callback data from Telebirr
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("=== TELEBIRR CALLBACK RECEIVED ===");
error_log("Raw Input: " . $input);
error_log("Parsed Data: " . json_encode($data));

// Verify signature
$app_secret = defined('TELEBIRR_API_KEY') ? TELEBIRR_API_KEY : '';

if (!$app_secret) {
    error_log("ERROR: Telebirr API key not configured");
    http_response_code(400);
    die('Error: API key not configured');
}

// Extract callback data
$transaction_id = $data['transactionId'] ?? null;
$out_trade_no = $data['outTradeNo'] ?? null;
$status = $data['status'] ?? null;
$amount = $data['totalAmount'] ?? null;

error_log("Transaction ID: " . ($transaction_id ?? 'N/A'));
error_log("Out Trade No: " . ($out_trade_no ?? 'N/A'));
error_log("Status: " . ($status ?? 'N/A'));
error_log("Amount: " . ($amount ?? 'N/A'));

// Extract order ID from outTradeNo (format: ORDER-{order_id}-{timestamp})
if (preg_match('/ORDER-(\d+)-/', $out_trade_no, $matches)) {
    $order_id = $matches[1];
    
    error_log("Extracted Order ID: " . $order_id);
    
    // Get order details
    $order = Orders::get_order_by_id($order_id);
    
    if (!$order) {
        error_log("ERROR: Order not found: " . $order_id);
        http_response_code(404);
        die('Order not found');
    }
    
    // Check payment status
    if ($status === 'SUCCESS' || $status === 'success') {
        error_log("Payment successful for order: " . $order_id);
        
        // Update payment status
        Database::execute_query(
            "UPDATE payments SET status = ?, transaction_id = ?, updated_at = CURRENT_TIMESTAMP 
             WHERE order_id = ?",
            ['Completed', $transaction_id, $order_id]
        );
        
        // Update order status
        Orders::update_order_status($order_id, 'Paid');
        
        error_log("Order status updated to Paid");
        
        // Send success response
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Payment confirmed']);
        
    } else if ($status === 'FAILED' || $status === 'failed') {
        error_log("Payment failed for order: " . $order_id);
        
        // Update payment status
        Database::execute_query(
            "UPDATE payments SET status = ?, transaction_id = ?, updated_at = CURRENT_TIMESTAMP 
             WHERE order_id = ?",
            ['Failed', $transaction_id, $order_id]
        );
        
        // Send failure response
        http_response_code(200);
        echo json_encode(['success' => false, 'message' => 'Payment failed']);
        
    } else {
        error_log("Unknown payment status: " . $status);
        
        // Update payment status
        Database::execute_query(
            "UPDATE payments SET status = ?, transaction_id = ?, updated_at = CURRENT_TIMESTAMP 
             WHERE order_id = ?",
            ['Pending', $transaction_id, $order_id]
        );
        
        http_response_code(200);
        echo json_encode(['success' => false, 'message' => 'Payment status unknown']);
    }
    
} else {
    error_log("ERROR: Could not extract order ID from: " . ($out_trade_no ?? 'N/A'));
    http_response_code(400);
    die('Invalid order reference');
}

error_log("=== END CALLBACK ===");
?>
