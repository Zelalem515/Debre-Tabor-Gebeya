<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/db.php';

Auth::require_role('admin');

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: orders.php');
    exit;
}

$order = Database::fetch_one("
    SELECT o.*, u.full_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.customer_id = u.id 
    WHERE o.id = ?
", [$order_id]);

if (!$order) {
    header('Location: orders.php');
    exit;
}

$items = Database::fetch_all("
    SELECT oi.*, p.name as product_name 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
", [$order_id]);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #2c3e50; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
        .info-group { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        .info-group label { font-weight: bold; color: #2c3e50; }
        .info-group p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: white; }
        tr:hover { background: #f9f9f9; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 0.9rem; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.paid { background: #d4edda; color: #155724; }
        .status.shipped { background: #cfe2ff; color: #084298; }
        .status.delivered { background: #d1e7dd; color: #0f5132; }
        .btn { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="container">
        <a href="orders.php" class="back-link">← Back to Orders</a>
        <h1>Order #<?php echo $order['id']; ?></h1>
        
        <div class="info-group">
            <label>Customer:</label>
            <p><?php echo htmlspecialchars($order['full_name'] ?? 'Unknown'); ?> (<?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?>)</p>
        </div>
        
        <div class="info-group">
            <label>Status:</label>
            <p><span class="status <?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></p>
        </div>
        
        <div class="info-group">
            <label>Shipping Address:</label>
            <p><?php echo htmlspecialchars($order['shipping_address']); ?></p>
        </div>
        
        <div class="info-group">
            <label>Payment Method:</label>
            <p><?php echo htmlspecialchars($order['payment_method']); ?></p>
        </div>
        
        <h2>Order Items</h2>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name'] ?? 'Unknown'); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['unit_price'], 2); ?> Br</td>
                    <td><?php echo number_format($item['subtotal'], 2); ?> Br</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="info-group" style="margin-top: 20px; text-align: right;">
            <label>Total Amount:</label>
            <p style="font-size: 1.5rem; font-weight: bold; color: #667eea;">
                <?php echo number_format($order['total_amount'], 2); ?> Br
            </p>
        </div>
        
        <div style="margin-top: 30px;">
            <button class="btn" onclick="alert('Update functionality coming soon')">Update Status</button>
        </div>
    </div>
</body>
</html>
