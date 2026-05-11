<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/localization.php';

Auth::require_role('customer');

$current_language = get_language();
$customer_id = Auth::get_user_id();
$customer_name = Auth::get_user_name();

$order_count = Orders::get_customer_order_count($customer_id);
$total_spent = Orders::get_customer_total_spent($customer_id);
$recent_orders = array_slice(Orders::get_customer_orders($customer_id), 0, 5);
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('dashboard', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 40px 20px;">
        <h1><?php echo translate('welcome', $current_language); ?>, <?php echo htmlspecialchars($customer_name); ?>!</h1>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div style="background: linear-gradient(135deg, var(--primary-color), #2d5a3d); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $order_count; ?></div>
                <div><?php echo translate('total_orders', $current_language); ?></div>
            </div>
            <div style="background: linear-gradient(135deg, var(--secondary-color), #c9a227); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo number_format($total_spent, 0); ?></div>
                <div><?php echo translate('total_spent', $current_language); ?> (<?php echo CURRENCY_SYMBOL; ?>)</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <a href="products.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; border: 2px solid var(--primary-color);">
                <div style="font-size: 2rem; margin-bottom: 10px;">🛍️</div>
                <div style="font-weight: bold; color: var(--primary-color);"><?php echo translate('browse_products', $current_language); ?></div>
            </a>
            <a href="cart.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; border: 2px solid var(--primary-color);">
                <div style="font-size: 2rem; margin-bottom: 10px;">🛒</div>
                <div style="font-weight: bold; color: var(--primary-color);"><?php echo translate('view_cart', $current_language); ?></div>
            </a>
            <a href="orders.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; border: 2px solid var(--primary-color);">
                <div style="font-size: 2rem; margin-bottom: 10px;">📦</div>
                <div style="font-weight: bold; color: var(--primary-color);"><?php echo translate('my_orders', $current_language); ?></div>
            </a>
        </div>

        <?php if (!empty($recent_orders)): ?>
            <h2><?php echo translate('recent_orders', $current_language); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?php echo translate('order_id', $current_language); ?></th>
                        <th><?php echo translate('date', $current_language); ?></th>
                        <th><?php echo translate('total', $current_language); ?></th>
                        <th><?php echo translate('status', $current_language); ?></th>
                        <th><?php echo translate('action', $current_language); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                            <td><?php echo number_format($order['total_amount'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary btn-sm"><?php echo translate('view', $current_language); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="../js/localization.js"></script>
</body>
</html>
