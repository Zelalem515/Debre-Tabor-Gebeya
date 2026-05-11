<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Shopping Cart Page
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/cart.php';
require_once __DIR__ . '/../php/localization.php';

// Require customer login
Auth::require_role('customer');

$current_language = get_language();
$cart = Cart::get_cart();
$cart_total = Cart::calculate_cart_total();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('shopping_cart', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 40px 20px;">
        <h1><?php echo translate('shopping_cart', $current_language); ?></h1>

        <?php if (empty($cart)): ?>
            <div class="alert alert-info" style="margin-bottom: 20px;">
                <?php echo translate('cart_is_empty', $current_language); ?>
            </div>
            <a href="products.php" class="btn btn-primary">
                <?php echo translate('continue_shopping', $current_language); ?>
            </a>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 300px; gap: 30px; margin-bottom: 40px;">
                <!-- Cart Items -->
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo translate('product', $current_language); ?></th>
                                <th><?php echo translate('price', $current_language); ?></th>
                                <th><?php echo translate('quantity', $current_language); ?></th>
                                <th><?php echo translate('subtotal', $current_language); ?></th>
                                <th><?php echo translate('action', $current_language); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $product_id => $item): ?>
                                <tr>
                                    <td>
                                        <a href="product-detail.php?id=<?php echo $product_id; ?>">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo number_format($item['price'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 5px;">
                                            <button onclick="updateQuantity(<?php echo $product_id; ?>, -1)" class="btn btn-secondary btn-sm">−</button>
                                            <input type="number" value="<?php echo $item['quantity']; ?>" 
                                                   onchange="updateQuantity(<?php echo $product_id; ?>, this.value - <?php echo $item['quantity']; ?>)"
                                                   min="1" style="width: 50px; text-align: center;">
                                            <button onclick="updateQuantity(<?php echo $product_id; ?>, 1)" class="btn btn-secondary btn-sm">+</button>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></td>
                                    <td>
                                        <button onclick="removeFromCart(<?php echo $product_id; ?>)" class="btn btn-danger btn-sm">
                                            <?php echo translate('remove', $current_language); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Cart Summary -->
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; height: fit-content;">
                    <h3><?php echo translate('order_summary', $current_language); ?></h3>
                    
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span><?php echo translate('subtotal', $current_language); ?>:</span>
                            <strong><?php echo number_format($cart_total, 2); ?> <?php echo CURRENCY_SYMBOL; ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span><?php echo translate('shipping', $current_language); ?>:</span>
                            <strong><?php echo translate('free', $current_language); ?></strong>
                        </div>
                        <hr>
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                            <span><?php echo translate('total', $current_language); ?>:</span>
                            <span><?php echo number_format($cart_total, 2); ?> <?php echo CURRENCY_SYMBOL; ?></span>
                        </div>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary btn-block" style="margin-bottom: 10px;">
                        <?php echo translate('proceed_to_checkout', $current_language); ?>
                    </a>
                    <a href="products.php" class="btn btn-secondary btn-block">
                        <?php echo translate('continue_shopping', $current_language); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../js/localization.js"></script>
    <script>
        function updateQuantity(productId, change) {
            fetch('../api/update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&change=' + change
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?php echo translate('error_updating_cart', $current_language); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo translate('error_updating_cart', $current_language); ?>');
            });
        }

        function removeFromCart(productId) {
            if (confirm('<?php echo translate('confirm_remove_item', $current_language); ?>')) {
                fetch('../api/remove-from-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || '<?php echo translate('error_removing_item', $current_language); ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo translate('error_removing_item', $current_language); ?>');
                });
            }
        }
    </script>
</body>
</html>
