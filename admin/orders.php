<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/db.php';

Auth::require_role('admin');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: white; }
        tr:hover { background: #f9f9f9; }
        .btn { padding: 8px 12px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 0.9rem; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.paid { background: #d4edda; color: #155724; }
        .status.shipped { background: #cfe2ff; color: #084298; }
        .status.delivered { background: #d1e7dd; color: #0f5132; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>Manage Orders</h1>
        
        <?php
        $orders = Database::fetch_all("
            SELECT o.id, o.customer_id, o.total_amount, o.status, o.created_at, u.full_name 
            FROM orders o 
            LEFT JOIN users u ON o.customer_id = u.id 
            ORDER BY o.created_at DESC
        ");
        ?>
        
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['full_name'] ?? 'Unknown'); ?></td>
                    <td><?php echo number_format($order['total_amount'], 2); ?> Br</td>
                    <td><span class="status <?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    <td>
                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p style="margin-top: 20px; color: #666;">Total Orders: <?php echo count($orders); ?></p>
    </div>
</body>
</html>
