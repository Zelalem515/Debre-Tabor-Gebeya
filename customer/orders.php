<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/localization.php';

Auth::require_role('customer');

$current_language = get_language();
$customer_id = Auth::get_user_id();
$orders = Orders::get_customer_orders($customer_id);
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('my_orders', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 40px 20px;">
        <h1><?php echo translate('my_orders', $current_language); ?></h1>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                <?php echo translate('no_orders_yet', $current_language); ?>
            </div>
            <a href="products.php" class="btn btn-primary"><?php echo translate('start_shopping', $current_language); ?></a>
        <?php else: ?>
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
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></td>
                            <td><?php echo number_format($order['total_amount'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></td>
                            <td>
                                <span style="padding: 5px 10px; border-radius: 4px; 
                                    <?php echo $order['status'] === 'Delivered' ? 'background: #d4edda; color: #155724;' : 
                                           ($order['status'] === 'Paid' ? 'background: #d1ecf1; color: #0c5460;' : 
                                            'background: #fff3cd; color: #856404;'); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
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
