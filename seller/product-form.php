<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Product Add/Edit Form Page
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/products.php';
require_once __DIR__ . '/../php/categories.php';
require_once __DIR__ . '/../php/localization.php';

// Require seller login
Auth::require_role('seller');

$current_language = get_language();
$seller_id = Auth::get_user_id();
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$error_message = '';
$success_message = '';

// If editing, get product details
if ($product_id) {
    $product = Products::get_product_by_id($product_id);
    
    if (!$product || $product['seller_id'] != $seller_id) {
        header('Location: products.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? '';
    
    // Validate inputs
    if (empty($name) || strlen($name) < 3) {
        $error_message = translate('product_name_required', $current_language);
    } elseif (empty($description) || strlen($description) < 10) {
        $error_message = translate('product_description_required', $current_language);
    } elseif (!is_numeric($price) || $price <= 0) {
        $error_message = translate('invalid_price', $current_language);
    } elseif (!is_numeric($stock_quantity) || $stock_quantity < 0) {
        $error_message = translate('invalid_stock', $current_language);
    } elseif (empty($category_id) || !is_numeric($category_id)) {
        $error_message = translate('category_required', $current_language);
    } else {
        if ($product_id) {
            // Update product
            $result = Products::update_product($product_id, $seller_id, [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category_id' => $category_id,
                'stock_quantity' => $stock_quantity
            ]);
            $new_product_id = $product_id;
        } else {
            // Add new product
            $result = Products::add_product(
                $seller_id,
                $name,
                $description,
                $price,
                $category_id,
                $stock_quantity
            );
            $new_product_id = $result['product_id'] ?? null;
        }
        
        if ($result['success'] && $new_product_id) {
            // Handle image uploads
            $upload_dir = __DIR__ . '/../images/uploads/';
            
            // Ensure upload directory exists with proper permissions
            if (!is_dir($upload_dir)) {
                if (!@mkdir($upload_dir, 0755, true)) {
                    error_log("Failed to create upload directory: $upload_dir");
                }
            }
            
            // Ensure directory is writable
            if (!is_writable($upload_dir)) {
                @chmod($upload_dir, 0755);
            }
            
            // If editing and new images are being uploaded, delete old images first
            if ($product_id && isset($_FILES['product_images']) && is_array($_FILES['product_images']['name'])) {
                // Check if any files were actually selected
                $has_files = false;
                foreach ($_FILES['product_images']['name'] as $filename) {
                    if (!empty($filename)) {
                        $has_files = true;
                        break;
                    }
                }
                
                // If new files are being uploaded, delete old images
                if ($has_files) {
                    $old_images = Products::get_product_images($product_id);
                    foreach ($old_images as $old_image) {
                        // Delete from filesystem
                        $old_file_path = __DIR__ . '/../' . $old_image['image_path'];
                        if (file_exists($old_file_path)) {
                            @unlink($old_file_path);
                        }
                        // Delete from database
                        Database::execute_query("DELETE FROM product_images WHERE id = ?", [$old_image['id']]);
                    }
                }
            }
            
            $image_count = 0;
            if (isset($_FILES['product_images']) && is_array($_FILES['product_images']['name'])) {
                foreach ($_FILES['product_images']['name'] as $key => $filename) {
                    if ($_FILES['product_images']['error'][$key] === UPLOAD_ERR_OK && $image_count < 3) {
                        $tmp_name = $_FILES['product_images']['tmp_name'][$key];
                        
                        // Get file extension
                        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        
                        // Validate image by extension
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (in_array($file_ext, $allowed_ext)) {
                            $new_filename = 'product_' . $new_product_id . '_' . ($image_count + 1) . '.' . $file_ext;
                            $file_path = $upload_dir . $new_filename;
                            
                            if (move_uploaded_file($tmp_name, $file_path)) {
                                // Save image path to database
                                $relative_path = 'images/uploads/' . $new_filename;
                                Products::add_product_image($new_product_id, $relative_path, $image_count, $image_count === 0);
                                $image_count++;
                            } else {
                                error_log("Failed to move uploaded file: $tmp_name to $file_path");
                            }
                        }
                    }
                }
            }
            
            $success_message = $result['message'];
            header('Refresh: 2; url=products.php');
        } else {
            $error_message = $result['message'];
        }
    }
}

$categories = Categories::get_all_categories();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product_id ? translate('edit_product', $current_language) : translate('add_product', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="auth-container">
        <div class="auth-box" style="max-width: 600px;">
            <h1><?php echo $product_id ? translate('edit_product', $current_language) : translate('add_product', $current_language); ?></h1>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="product-form" novalidate enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name"><?php echo translate('product_name', $current_language); ?></label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($product['name'] ?? $_POST['name'] ?? ''); ?>">
                    <span class="error-message" id="name-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="description"><?php echo translate('description', $current_language); ?></label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description'] ?? $_POST['description'] ?? ''); ?></textarea>
                    <span class="error-message" id="description-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="category_id"><?php echo translate('category', $current_language); ?></label>
                    <select id="category_id" name="category_id" required>
                        <option value=""><?php echo translate('select_category', $current_language); ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($product['category_id'] ?? $_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error-message" id="category_id-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="price"><?php echo translate('price', $current_language); ?> (<?php echo CURRENCY_SYMBOL; ?>)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required 
                           value="<?php echo htmlspecialchars($product['price'] ?? $_POST['price'] ?? ''); ?>">
                    <span class="error-message" id="price-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="stock_quantity"><?php echo translate('stock_quantity', $current_language); ?></label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0" required 
                           value="<?php echo htmlspecialchars($product['stock_quantity'] ?? $_POST['stock_quantity'] ?? '0'); ?>">
                    <span class="error-message" id="stock_quantity-error"></span>
                </div>

                <div class="form-group">
                    <label for="product_images">Product Images (Upload 3 different views)</label>
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 2px dashed #ddd;">
                        <input type="file" id="product_images" name="product_images[]" multiple accept="image/*" 
                               style="display: none;">
                        <button type="button" onclick="document.getElementById('product_images').click()" 
                                class="btn btn-secondary" style="width: 100%; margin-bottom: 10px;">
                            📁 Choose Images (Max 3)
                        </button>
                        <div id="image-preview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;"></div>
                        <div id="image-count" style="color: #2c3e50; font-weight: bold; margin-top: 10px; display: none;">
                            Selected: <span id="count-number">0</span> / 3 images
                        </div>
                        <small style="color: #666; display: block; margin-top: 10px;">
                            ✓ Supported formats: JPG, PNG, GIF, WebP<br>
                            ✓ Upload up to 3 images for different product views<br>
                            
                            </small>
                    </div>
                    <span class="error-message" id="images-error"></span>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <?php echo $product_id ? translate('update_product', $current_language) : translate('add_product', $current_language); ?>
                    </button>
                    <a href="products.php" class="btn btn-secondary" style="flex: 1; text-align: center;">
                        <?php echo translate('cancel', $current_language); ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../js/localization.js"></script>
    <script src="../js/validation.js"></script>
    <script>
        // Image preview handler
        document.getElementById('product_images').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            const files = Array.from(e.target.files).slice(0, 3);
            
            // Update count display
            const countDisplay = document.getElementById('image-count');
            const countNumber = document.getElementById('count-number');
            
            if (files.length > 0) {
                countDisplay.style.display = 'block';
                countNumber.textContent = files.length;
            } else {
                countDisplay.style.display = 'none';
            }
            
            if (files.length > 3) {
                document.getElementById('images-error').textContent = '❌ Maximum 3 images allowed. Only first 3 will be uploaded.';
            } else if (files.length > 0) {
                document.getElementById('images-error').textContent = '';
            }
            
            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const div = document.createElement('div');
                    div.style.position = 'relative';
                    div.style.border = '2px solid #2c3e50';
                    div.style.borderRadius = '4px';
                    div.innerHTML = `
                        <img src="${event.target.result}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 2px;">
                        <small style="position: absolute; bottom: 5px; left: 5px; background: rgba(0,0,0,0.8); color: white; padding: 3px 6px; border-radius: 3px; font-weight: bold;">
                            Image ${index + 1}
                        </small>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
        
        // Client-side validation
        document.getElementById('product-form').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            
            // Validate name
            const name = document.getElementById('name').value.trim();
            if (name.length < 3) {
                document.getElementById('name-error').textContent = '<?php echo translate('product_name_min_3', $current_language); ?>';
                isValid = false;
            }
            
            // Validate description
            const description = document.getElementById('description').value.trim();
            if (description.length < 10) {
                document.getElementById('description-error').textContent = '<?php echo translate('description_min_10', $current_language); ?>';
                isValid = false;
            }
            
            // Validate category
            const categoryId = document.getElementById('category_id').value;
            if (!categoryId) {
                document.getElementById('category_id-error').textContent = '<?php echo translate('category_required', $current_language); ?>';
                isValid = false;
            }
            
            // Validate price
            const price = parseFloat(document.getElementById('price').value);
            if (isNaN(price) || price <= 0) {
                document.getElementById('price-error').textContent = '<?php echo translate('price_must_be_positive', $current_language); ?>';
                isValid = false;
            }
            
            // Validate stock
            const stock = parseInt(document.getElementById('stock_quantity').value);
            if (isNaN(stock) || stock < 0) {
                document.getElementById('stock_quantity-error').textContent = '<?php echo translate('stock_cannot_be_negative', $current_language); ?>';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
