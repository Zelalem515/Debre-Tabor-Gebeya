<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/products.php';
require_once __DIR__ . '/../php/localization.php';

Auth::require_role('admin');

$current_language = get_language();

$user_count = 0;
$users = Database::fetch_all("SELECT COUNT(*) as count FROM users");
if (!empty($users)) $user_count = $users[0]['count'];

$product_count = Products::get_product_count();
$order_count = Orders::get_order_count();
$total_revenue = Orders::get_total_revenue();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('admin_dashboard', $current_language); ?> - DEBRE TABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 40px 20px;">
        <h1><?php echo translate('admin_dashboard', $current_language); ?></h1>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $user_count; ?></div>
                <div><?php echo translate('total_users', $current_language); ?></div>
            </div>
            <div style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $product_count; ?></div>
                <div><?php echo translate('total_products', $current_language); ?></div>
            </div>
            <div style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $order_count; ?></div>
                <div><?php echo translate('total_orders', $current_language); ?></div>
            </div>
            <div style="background: linear-gradient(135deg, #43e97b, #38f9d7); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo number_format($total_revenue, 0); ?></div>
                <div><?php echo translate('total_revenue', $current_language); ?> (<?php echo CURRENCY_SYMBOL; ?>)</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <a href="users.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; border: 2px solid var(--primary-color);">
                <div style="font-size: 2rem; margin-bottom: 10px;">👥</div>
                <div style="font-weight: bold; color: var(--primary-color);"><?php echo translate('manage_users', $current_language); ?></div>
            </a>
            <a href="categories.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; border: 2px solid var(--primary-color);">
                <div style="font-size: 2rem; margin-bottom: 10px;">📂</div>
                <div style="font-weight: bold; color: var(--primary-color);"><?php echo translate('manage_categories', $current_language); ?></div>
            </a>
            <a href="orders.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; border: 2px solid var(--primary-color);">
                <div style="font-size: 2rem; margin-bottom: 10px;">📋</div>
                <div style="font-weight: bold; color: var(--primary-color);"><?php echo translate('manage_orders', $current_language); ?></div>
            </a>
            <a href="reports.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; border: 2px solid var(--primary-color);">
                <div style="font-size: 2rem; margin-bottom: 10px;">📊</div>
                <div style="font-weight: bold; color: var(--primary-color);"><?php echo translate('view_reports', $current_language); ?></div>
            </a>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="../js/localization.js"></script>
</body>
</html>
