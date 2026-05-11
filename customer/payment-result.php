<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Payment Result Page
 * 
 * This page shows the payment result after Telebirr redirects back
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/payment.php';

session_start();

$order_id = $_GET['order_id'] ?? null;
$payment_status = $_GET['status'] ?? null;

if (!$order_id) {
    die('Error: Order ID is required');
}

// Get order details
$order = Orders::get_order_by_id($order_id);

if (!$order) {
    die('Error: Order not found');
}

// Get payment details
$payment = Payment::get_payment_by_order_id($order_id);

// Determine payment status
$is_paid = $payment && $payment['status'] === 'Completed';
$is_failed = $payment && $payment['status'] === 'Failed';
$is_pending = $payment && $payment['status'] === 'Pending';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Result - DEBRETABOR GEBEYA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .message {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #666;
            font-weight: 500;
        }
        
        .detail-value {
            color: #333;
            font-weight: 600;
        }
        
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            margin: 10px 5px;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .button.secondary {
            background: #6c757d;
        }
        
        .button.secondary:hover {
            box-shadow: 0 5px 20px rgba(108, 117, 125, 0.4);
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($is_paid): ?>
            <div class="icon">✅</div>
            <span class="status-badge success">PAYMENT SUCCESSFUL</span>
            <h1>Payment Confirmed!</h1>
            <p class="message">
                Your payment has been successfully processed. Your order is now confirmed and will be prepared for delivery.
            </p>
            
        <?php elseif ($is_failed): ?>
            <div class="icon">❌</div>
            <span class="status-badge failed">PAYMENT FAILED</span>
            <h1>Payment Failed</h1>
            <p class="message">
                Unfortunately, your payment could not be processed. Please try again or contact support.
            </p>
            
        <?php elseif ($is_pending): ?>
            <div class="icon">⏳</div>
            <span class="status-badge pending">PAYMENT PENDING</span>
            <h1>Payment Pending</h1>
            <p class="message">
                Your payment is being processed. Please wait a moment and refresh this page.
            </p>
            
        <?php else: ?>
            <div class="icon">❓</div>
            <h1>Payment Status Unknown</h1>
            <p class="message">
                We couldn't determine the status of your payment. Please contact support.
            </p>
        <?php endif; ?>
        
        <div class="details">
            <div class="detail-item">
                <span class="detail-label">Order ID:</span>
                <span class="detail-value">#<?php echo htmlspecialchars($order_id); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Amount:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['total_amount']); ?> ETB</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'Unknown'); ?></span>
            </div>
            <?php if ($payment): ?>
            <div class="detail-item">
                <span class="detail-label">Transaction ID:</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment['transaction_id']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status:</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment['status']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div>
            <a href="/customer/orders.php" class="button">View My Orders</a>
            <a href="/index.php" class="button secondary">Continue Shopping</a>
        </div>
    </div>
</body>
</html>
