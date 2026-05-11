<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Payment Success Page
 * 
 * Simple success page shown after payment
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/payment.php';
require_once __DIR__ . '/../php/localization.php';

$current_language = get_language();
$order_id = $_GET['order_id'] ?? null;
$status = $_GET['status'] ?? 'unknown';

if (!$order_id) {
    $order_id = $_SESSION['current_order_id'] ?? null;
}

$order = null;
$payment = null;
$is_success = false;

if ($order_id) {
    $order = Orders::get_order_by_id($order_id);
    $payment = Payment::get_payment_by_order_id($order_id);
    $is_success = ($status === 'success' || ($payment && $payment['status'] === 'Completed'));
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_success ? translate('payment_successful', $current_language) : translate('payment', $current_language); ?> - DEBRETABOR GEBEYA</title>
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
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 0.6s ease-in-out;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        h1 {
            color: #28a745;
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .subtitle {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .order-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 15px;
        }
        
        .detail-row:last-child {
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
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .button {
            display: inline-block;
            padding: 14px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .button.secondary {
            background: #6c757d;
        }
        
        .button.secondary:hover {
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.4);
        }
        
        .success-badge {
            display: inline-block;
            background: #d4edda;
            color: #155724;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
            font-size: 14px;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($is_success && $order): ?>
            <div class="success-icon">✅</div>
            <span class="success-badge">PAYMENT SUCCESSFUL</span>
            <h1>Payment Confirmed!</h1>
            <p class="subtitle">
                Your payment has been successfully processed. Your order is now confirmed and will be prepared for delivery.
            </p>
            
            <div class="info-box">
                ℹ️ You will receive an email confirmation shortly with your order details and tracking information.
            </div>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value">#<?php echo htmlspecialchars($order_id); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['total_amount']); ?> ETB</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'Unknown'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Delivery Address:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                </div>
                <?php if ($payment): ?>
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($payment['transaction_id']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="button-group">
                <a href="orders.php" class="button">📦 View My Orders</a>
                <a href="../index.php" class="button secondary">🛍️ Continue Shopping</a>
            </div>
            
        <?php else: ?>
            <div class="success-icon">⏳</div>
            <h1>Processing Payment...</h1>
            <p class="subtitle">
                Your payment is being processed. Please wait a moment.
            </p>
            
            <?php if ($order): ?>
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value">#<?php echo htmlspecialchars($order_id); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['total_amount']); ?> ETB</span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="button-group">
                <button class="button" onclick="location.reload()">🔄 Refresh Page</button>
                <a href="../index.php" class="button secondary">🏠 Go Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
