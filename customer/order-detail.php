<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/payment.php';
require_once __DIR__ . '/../php/localization.php';

Auth::require_role('customer');

$current_language = get_language();
$customer_id = Auth::get_user_id();
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$order = Orders::get_order_by_id($order_id);

if (!$order || $order['customer_id'] != $customer_id) {
    header('Location: orders.php');
    exit;
}

$order_items = Orders::get_order_items($order_id);
$payment = Payment::get_payment_by_order_id($order_id);
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('order_details', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 40px 20px;">
        <a href="orders.php" class="btn btn-secondary btn-sm" style="margin-bottom: 20px;">← <?php echo translate('back_to_orders', $current_language); ?></a>

        <h1><?php echo translate('order', $current_language); ?> #<?php echo $order_id; ?></h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
            <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
                <h3><?php echo translate('order_information', $current_language); ?></h3>
                <p><strong><?php echo translate('order_date', $current_language); ?>:</strong> <?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></p>
                <p><strong><?php echo translate('status', $current_language); ?>:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                <p><strong><?php echo translate('total_amount', $current_language); ?>:</strong> <?php echo number_format($order['total_amount'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></p>
                <p><strong><?php echo translate('shipping_address', $current_language); ?>:</strong></p>
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>

            <?php if ($payment): ?>
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
                    <h3><?php echo translate('payment_information', $current_language); ?></h3>
                    <p><strong><?php echo translate('payment_method', $current_language); ?>:</strong> <?php echo htmlspecialchars($payment['payment_method']); ?></p>
                    <p><strong><?php echo translate('payment_status', $current_language); ?>:</strong> <?php echo htmlspecialchars($payment['status']); ?></p>
                    <p><strong><?php echo translate('transaction_id', $current_language); ?>:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                    <p><strong><?php echo translate('amount', $current_language); ?>:</strong> <?php echo number_format($payment['amount'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></p>
                </div>
            <?php endif; ?>
        </div>

        <h3><?php echo translate('order_items', $current_language); ?></h3>
        <table>
            <thead>
                <tr>
                    <th><?php echo translate('product', $current_language); ?></th>
                    <th><?php echo translate('seller', $current_language); ?></th>
                    <th><?php echo translate('quantity', $current_language); ?></th>
                    <th><?php echo translate('unit_price', $current_language); ?></th>
                    <th><?php echo translate('subtotal', $current_language); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['seller_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['unit_price'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></td>
                        <td><?php echo number_format($item['subtotal'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="../js/localization.js"></script>
</body>
</html>
