<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Seller Product Management Page
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/products.php';
require_once __DIR__ . '/../php/localization.php';

// Require seller login
Auth::require_role('seller');

$current_language = get_language();
$seller_id = Auth::get_user_id();
$products = Products::get_seller_products($seller_id);
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('manage_products', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 40px 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1><?php echo translate('manage_products', $current_language); ?></h1>
            <a href="product-form.php" class="btn btn-primary">
                + <?php echo translate('add_product', $current_language); ?>
            </a>
        </div>

        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                <?php echo translate('no_products_yet', $current_language); ?>
                <a href="product-form.php" style="margin-left: 10px;"><?php echo translate('add_your_first_product', $current_language); ?></a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th><?php echo translate('product_name', $current_language); ?></th>
                        <th><?php echo translate('price', $current_language); ?></th>
                        <th><?php echo translate('stock', $current_language); ?></th>
                        <th><?php echo translate('created_date', $current_language); ?></th>
                        <th><?php echo translate('actions', $current_language); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo number_format($product['price'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></td>
                            <td><?php echo $product['stock_quantity']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($product['created_at'])); ?></td>
                            <td>
                                <a href="product-form.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">
                                    <?php echo translate('edit', $current_language); ?>
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                    <?php echo translate('delete', $current_language); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../js/localization.js"></script>
    <script>
        function deleteProduct(productId) {
            if (confirm('<?php echo translate('confirm_delete_product', $current_language); ?>')) {
                fetch('../api/delete-product.php', {
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
                        alert(data.message || '<?php echo translate('error_deleting_product', $current_language); ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo translate('error_deleting_product', $current_language); ?>');
                });
            }
        }
    </script>
</body>
</html>
