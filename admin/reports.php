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
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #2c3e50; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; }
        .stat-card h3 { margin: 0 0 10px 0; font-size: 0.9rem; opacity: 0.9; }
        .stat-card .value { font-size: 2rem; font-weight: bold; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: white; }
        tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>System Reports</h1>
        
        <?php
        // Get statistics
        $total_users = Database::fetch_one("SELECT COUNT(*) as count FROM users");
        $total_products = Database::fetch_one("SELECT COUNT(*) as count FROM products");
        $total_orders = Database::fetch_one("SELECT COUNT(*) as count FROM orders");
        $total_revenue = Database::fetch_one("SELECT SUM(total_amount) as total FROM orders WHERE status = 'Delivered'");
        
        $users_count = $total_users['count'] ?? 0;
        $products_count = $total_products['count'] ?? 0;
        $orders_count = $total_orders['count'] ?? 0;
        $revenue = $total_revenue['total'] ?? 0;
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="value"><?php echo $users_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="value"><?php echo $products_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="value"><?php echo $orders_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="value"><?php echo number_format($revenue, 0); ?> Br</div>
            </div>
        </div>
        
        <h2 style="margin-top: 40px;">Sales by Category</h2>
        <?php
        $sales_by_category = Database::fetch_all("
            SELECT c.name, COUNT(oi.id) as count, SUM(oi.subtotal) as total
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            GROUP BY c.id, c.name
            ORDER BY total DESC
        ");
        ?>
        
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Items Sold</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales_by_category as $sale): ?>
                <tr>
                    <td><?php echo htmlspecialchars($sale['name'] ?? 'Unknown'); ?></td>
                    <td><?php echo $sale['count']; ?></td>
                    <td><?php echo number_format($sale['total'] ?? 0, 2); ?> Br</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
