<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Product Detail Page
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/products.php';
require_once __DIR__ . '/../php/localization.php';
require_once __DIR__ . '/../php/messaging.php';

// Require customer login
Auth::require_role('customer');

$current_language = get_language();
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

$product = Products::get_product_by_id($product_id);

if (!$product) {
    header('Location: products.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/products.css">
    <link rel="stylesheet" href="../css/messaging.css">
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="product-detail">
        <div class="container">
            <a href="products.php" class="btn btn-secondary btn-sm" style="margin-bottom: 20px;">
                ← <?php echo translate('back_to_products', $current_language); ?>
            </a>

            <?php
            // Get all product images
            $product_images = Products::get_product_images($product_id);
            ?>

            <!-- Main Layout: Image + Details on Left, Seller Card on Right -->
            <div style="display: flex; gap: 30px; margin-bottom: 40px;">
                <!-- Left Column: Image and Product Details -->
                <div style="flex: 1; min-width: 300px;">
                    <!-- Images Section -->
                    <div style="margin-bottom: 30px;">
                    <?php if (!empty($product_images)): ?>
                        <!-- Image Carousel with Arrow Navigation (Like jiji.et) -->
                        <div class="image-carousel" style="position: relative; width: 100%; max-width: 500px; margin: 0 auto;">
                            <!-- Main Image -->
                            <div style="position: relative; width: 100%; background: #f5f5f5; border-radius: 8px; overflow: hidden;">
                                <?php 
                                    $first_img_path = $product_images[0]['image_path'];
                                    // Remove any existing ../ prefixes
                                    $first_img_path = str_replace('../', '', $first_img_path);
                                    // Add ../ to go up one level from customer/ directory
                                    $first_img_path = '../' . $first_img_path;
                                ?>
                                <img id="mainImage" 
                                     src="<?php echo htmlspecialchars($first_img_path); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="width: 100%; height: 400px; object-fit: cover; display: block;">
                                
                                <!-- Image Counter -->
                                <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 4px; font-size: 12px;">
                                    <span id="imageCounter">1</span> / <span id="totalImages"><?php echo count($product_images); ?></span>
                                </div>
                                
                                <!-- Left Arrow -->
                                <button type="button" 
                                        onclick="previousImage()"
                                        style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: background 0.3s; opacity: <?php echo count($product_images) > 1 ? '1' : '0.3'; ?>;" 
                                        <?php echo count($product_images) <= 1 ? 'disabled' : ''; ?>>
                                    ‹
                                </button>
                                
                                <!-- Right Arrow -->
                                <button type="button" 
                                        onclick="nextImage()"
                                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: background 0.3s; opacity: <?php echo count($product_images) > 1 ? '1' : '0.3'; ?>;" 
                                        <?php echo count($product_images) <= 1 ? 'disabled' : ''; ?>>
                                    ›
                                </button>
                            </div>
                            
                            <!-- Thumbnail Indicators -->
                            <div style="display: flex; gap: 8px; margin-top: 12px; justify-content: center;">
                                <?php foreach ($product_images as $index => $img): ?>
                                    <button type="button" 
                                            class="image-indicator <?php echo $index === 0 ? 'active' : ''; ?>"
                                            onclick="goToImage(<?php echo $index; ?>)"
                                            style="width: 10px; height: 10px; border-radius: 50%; border: none; cursor: pointer; background: <?php echo $index === 0 ? '#2c3e50' : '#ddd'; ?>; transition: background 0.3s;">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif ($product['image_path']): 
                        $fallback_path = $product['image_path'];
                        if (strpos($fallback_path, '../') === false) {
                            $fallback_path = '../' . $fallback_path;
                        }
                    ?>
                        <img src="<?php echo htmlspecialchars($fallback_path); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             style="width: 100%; height: 400px; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                        <div class="product-image-placeholder" style="width: 100%; height: 400px; display: flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 8px; font-size: 4rem;">📦</div>
                    <?php endif; ?>
                    </div>
                    <!-- End Images Section -->

                    <!-- Product Details Section Below Image -->
                    <div class="product-detail-info" style="background: white; padding: 20px; border-radius: 8px;">
                        <h1 style="margin: 0 0 15px 0; font-size: 24px;"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="product-detail-price" style="font-size: 28px; font-weight: bold; color: #27ae60; margin-bottom: 15px;">
                            <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($product['price'], 0); ?>
                        </div>

                        <div class="product-detail-stock" style="margin-bottom: 20px;">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="stock-status stock-in" style="background: #d4edda; color: #155724; padding: 8px 12px; border-radius: 4px; display: inline-block;">
                                    ✓ <?php echo translate('in_stock', $current_language); ?> (<?php echo $product['stock_quantity']; ?> available)
                                </span>
                            <?php else: ?>
                                <span class="stock-status stock-out" style="background: #f8d7da; color: #721c24; padding: 8px 12px; border-radius: 4px; display: inline-block;">
                                    ✗ <?php echo translate('out_of_stock', $current_language); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="product-detail-description" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #333;">Description</h4>
                            <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.6;"><?php echo nl2br(htmlspecialchars(substr($product['description'], 0, 300))); ?></p>
                        </div>

                        <div class="product-detail-seller" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #333;">Seller Information</h4>
                            <p style="margin: 5px 0; color: #666; font-size: 14px;"><strong>Seller:</strong> <?php echo htmlspecialchars($product['seller_name']); ?></p>
                            <p style="margin: 5px 0; color: #666; font-size: 14px;"><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
                        </div>

                        <?php if ($product['stock_quantity'] > 0): ?>
                            <div class="quantity-selector" style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                                <label for="quantity" style="font-weight: bold; color: #333;">Quantity:</label>
                                <button type="button" onclick="decreaseQuantity()" style="width: 36px; height: 36px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; font-size: 18px;">−</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                       max="<?php echo $product['stock_quantity']; ?>" style="width: 60px; height: 36px; border: 1px solid #ddd; text-align: center; border-radius: 4px; font-size: 14px;">
                                <button type="button" onclick="increaseQuantity(<?php echo $product['stock_quantity']; ?>)" style="width: 36px; height: 36px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; font-size: 18px;">+</button>
                            </div>

                            <div class="product-detail-actions" style="display: flex; gap: 10px;">
                                <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)" style="flex: 1; background: #27ae60; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;">
                                    🛒 Add to Cart
                                </button>
                                <a href="../customer/cart.php" class="btn btn-secondary" style="flex: 1; background: #f39c12; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; text-align: center; text-decoration: none;">
                                    👁️ View Cart
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="product-detail-actions" style="display: flex; gap: 10px;">
                                <button class="btn btn-primary" disabled style="flex: 1; background: #ccc; color: #666; border: none; padding: 12px; border-radius: 6px; cursor: not-allowed; font-weight: bold; font-size: 14px;">
                                    Out of Stock
                                </button>
                                <a href="products.php" class="btn btn-secondary" style="flex: 1; background: #3498db; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px; text-align: center; text-decoration: none;">
                                    Continue Shopping
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: Seller Info Card and Chat -->
                <div style="flex: 0 0 350px; display: flex; flex-direction: column; gap: 20px;">
                    <!-- Seller Info Card -->
                    <div class="seller-info-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: white;">
                        <div class="seller-header" style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <div class="seller-avatar" style="width: 70px; height: 70px; border-radius: 50%; background: #e0e0e0; display: flex; align-items: center; justify-content: center; font-size: 32px; flex-shrink: 0;">👤</div>
                            <div class="seller-details">
                                <h3 style="margin: 0 0 5px 0; font-size: 18px;"><?php echo htmlspecialchars($product['seller_name']); ?></h3>
                                <p style="margin: 0; color: #f39c12; font-size: 14px;">⭐ Typically replies in a few hours</p>
                            </div>
                        </div>
                        
                        <div class="seller-contact" style="border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 15px 0; margin: 15px 0;">
                            <p style="margin: 0; color: #666; font-size: 14px;">📞 Contact Seller</p>
                        </div>
                        
                        <div class="seller-actions" style="display: flex; flex-direction: column; gap: 10px;">
                            <button class="btn-chat" id="startChatBtn" onclick="startChat()" style="background: #27ae60; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;">💬 Start Chat</button>
                            <button class="btn-save" style="background: #f0f0f0; color: #333; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;">❤️ Save</button>
                            <button class="btn-report" style="background: #fce4ec; color: #c2185b; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;">🚩 Report</button>
                        </div>
                    </div>

                    <!-- Chat Box Below Seller Card -->
                    <div id="chatBox" class="chat-box" style="display: none; border: 1px solid #ddd; border-radius: 8px; background: white; overflow: hidden; flex: 1; min-height: 400px; display: flex; flex-direction: column;">
                        <div class="chat-box-header" style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; font-size: 16px;">💬 Chat with Seller</h3>
                            <button class="chat-close-btn" onclick="closeChat()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #999;">✕</button>
                        </div>
                        
                        <div class="chat-box-messages" id="chatMessages" style="flex: 1; overflow-y: auto; padding: 15px; background: white;"></div>
                        
                        <div class="chat-box-input" style="border-top: 1px solid #ddd; padding: 15px; display: flex; gap: 10px; background: #f8f9fa;">
                            <input type="text" id="messageInput" placeholder="Type your message..." autocomplete="off" style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 6px; font-size: 14px;">
                            <button onclick="sendMessage()" style="background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">Send</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../js/localization.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/messaging.js"></script>
    <script>
        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            if (quantityInput.value > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }

        function increaseQuantity(maxStock) {
            const quantityInput = document.getElementById('quantity');
            if (parseInt(quantityInput.value) < maxStock) {
                quantityInput.value = parseInt(quantityInput.value) + 1;
            }
        }

        // Image carousel data
        const images = [
            <?php foreach ($product_images as $index => $img): 
                $img_path = $img['image_path'];
                // Remove any existing ../ prefixes
                $img_path = str_replace('../', '', $img_path);
                // Add ../ to go up one level from customer/ directory
                $img_path = '../' . $img_path;
            ?>
                '<?php echo htmlspecialchars($img_path); ?>'<?php echo $index < count($product_images) - 1 ? ',' : ''; ?>
            <?php endforeach; ?>
        ];
        
        let currentImageIndex = 0;
        
        // Go to specific image
        function goToImage(index) {
            if (index >= 0 && index < images.length) {
                currentImageIndex = index;
                updateImage();
            }
        }
        
        // Next image
        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            updateImage();
        }
        
        // Previous image
        function previousImage() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            updateImage();
        }
        
        // Update image display
        function updateImage() {
            document.getElementById('mainImage').src = images[currentImageIndex];
            document.getElementById('imageCounter').textContent = currentImageIndex + 1;
            
            // Update indicators
            document.querySelectorAll('.image-indicator').forEach((btn, index) => {
                btn.style.background = index === currentImageIndex ? '#2c3e50' : '#ddd';
            });
        }

        function addToCart(productId) {
            const quantity = document.getElementById('quantity').value;
            
            fetch('../api/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('<?php echo translate('product_added_to_cart', $current_language); ?>', 'success');
                    updateCartCount();
                    // Reset quantity
                    document.getElementById('quantity').value = 1;
                } else {
                    showNotification(data.message || '<?php echo translate('error_adding_to_cart', $current_language); ?>', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('<?php echo translate('error_adding_to_cart', $current_language); ?>', 'error');
            });
        }

        // Initialize messaging system - call directly since script is already loaded
        initializeMessaging(<?php echo $product_id; ?>, <?php echo $product['seller_id']; ?>);
    </script>
</body>
</html>
