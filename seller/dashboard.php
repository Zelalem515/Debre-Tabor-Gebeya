<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/products.php';
require_once __DIR__ . '/../php/localization.php';

Auth::require_role('seller');

$current_language = get_language();
$seller_id = Auth::get_user_id();
$seller_name = Auth::get_user_name();

$product_count = Products::get_seller_product_count($seller_id);
$products = Products::get_seller_products($seller_id, 5);
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('dashboard', $current_language); ?> - DEBRE TABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 40px 20px;">
        <h1><?php echo translate('welcome', $current_language); ?>, <?php echo htmlspecialchars($seller_name); ?>!</h1>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <div style="background: linear-gradient(135deg, var(--primary-color), #2d5a3d); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;"><?php echo $product_count; ?></div>
                <div><?php echo translate('total_products', $current_language); ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <a href="products.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; border: 2px solid var(--primary-color);">
                <div style="font-size: 2rem; margin-bottom: 10px;">📦</div>
                <div style="font-weight: bold; color: var(--primary-color);"><?php echo translate('manage_products', $current_language); ?></div>
            </a>
            <a href="chat.php" style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; border: 2px solid var(--primary-color);">
                <div style="font-size: 2rem; margin-bottom: 10px;">💬</div>
                <div style="font-weight: bold; color: var(--primary-color);"><?php echo translate('messages', $current_language); ?></div>
            </a>
        </div>

        <h2><?php echo translate('recent_products', $current_language); ?></h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <?php if (empty($products)): ?>
                <p><?php echo translate('no_products', $current_language); ?></p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <?php 
                        // Get product images
                        $product_images = Products::get_product_images($product['id']);
                        $image_path = null;
                        
                        if (!empty($product_images)) {
                            $image_path = $product_images[0]['image_path'];
                            // Remove any existing ../ prefixes
                            $image_path = str_replace('../', '', $image_path);
                            // Add ../ to go up one level from seller/ directory
                            $image_path = '../' . $image_path;
                        } elseif ($product['image_url']) {
                            $image_path = $product['image_url'];
                            if (strpos($image_path, '../') === false) {
                                $image_path = '../' . $image_path;
                            }
                        }
                    ?>
                    <div style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                        <div style="background: #f0f0f0; height: 150px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <?php if ($image_path): ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <span style="font-size: 3rem;">📦</span>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 15px;">
                            <h3 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-weight: bold; color: var(--primary-color);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price'], 0); ?></span>
                                <a href="product-form.php?id=<?php echo $product['id']; ?>" style="color: var(--primary-color); text-decoration: none;">Edit</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
