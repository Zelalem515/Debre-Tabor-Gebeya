<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Customer Products Browsing Page
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/products.php';
require_once __DIR__ . '/../php/categories.php';
require_once __DIR__ . '/../php/localization.php';

// Require customer login
Auth::require_role('customer');

$current_language = get_language();
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 12;
$offset = ($current_page - 1) * $items_per_page;

// Get filter and search parameters
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get products based on filters
if ($search_term) {
    $products = Products::search_products($search_term, $items_per_page, $offset);
    $total_products = count(Products::search_products($search_term));
} elseif ($category_id) {
    $products = Products::get_products_by_category($category_id, $items_per_page, $offset);
    $total_products = count(Products::get_products_by_category($category_id));
} else {
    $products = Products::get_all_products($items_per_page, $offset);
    $total_products = Products::get_product_count();
}

// Sort products
if ($sort_by === 'price_low') {
    usort($products, function($a, $b) {
        return $a['price'] - $b['price'];
    });
} elseif ($sort_by === 'price_high') {
    usort($products, function($a, $b) {
        return $b['price'] - $a['price'];
    });
}

$total_pages = ceil($total_products / $items_per_page);
$categories = Categories::get_all_categories();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('browse_products', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/products.css">
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="products-section">
        <div class="container">
            <div class="products-header">
                <h1><?php echo translate('browse_products', $current_language); ?></h1>
                <div class="products-controls">
                    <form method="GET" class="search-box">
                        <input type="text" name="search" placeholder="<?php echo translate('search', $current_language); ?>" 
                               value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit"><?php echo translate('search', $current_language); ?></button>
                    </form>
                    
                    <div class="filter-box">
                        <label><?php echo translate('filter', $current_language); ?>:</label>
                        <select name="category" onchange="filterByCategory(this.value)">
                            <option value=""><?php echo translate('all_categories', $current_language); ?></option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="sort-box">
                        <label><?php echo translate('sort', $current_language); ?>:</label>
                        <select name="sort" onchange="sortProducts(this.value)">
                            <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>
                                <?php echo translate('newest', $current_language); ?>
                            </option>
                            <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>
                                <?php echo translate('price_low_to_high', $current_language); ?>
                            </option>
                            <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>
                                <?php echo translate('price_high_to_low', $current_language); ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    <?php echo translate('no_products_found', $current_language); ?>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): 
                        // Get primary product image
                        $primary_image = Products::get_primary_product_image($product['id']);
                        $image_path = $primary_image ? $primary_image['image_path'] : $product['image_path'];
                        // Adjust path for current directory level (customer/ is one level deep)
                        if ($image_path) {
                            // Remove any existing ../ prefixes
                            $image_path = str_replace('../', '', $image_path);
                            // Add ../ to go up one level from customer/ directory
                            $image_path = '../' . $image_path;
                        }
                    ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($image_path): ?>
                                    <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         style="width: 100%; height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="product-image-placeholder" style="width: 100%; height: 200px; display: flex; align-items: center; justify-content: center; background: #f0f0f0; font-size: 3rem;">📦</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                                </p>
                                <div class="product-price">
                                    <?php echo number_format($product['price'], 2); ?> <?php echo CURRENCY_SYMBOL; ?>
                                </div>
                                <div class="product-stock">
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <span class="stock-status stock-in">
                                            <?php echo translate('in_stock', $current_language); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="stock-status stock-out">
                                            <?php echo translate('out_of_stock', $current_language); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-secondary btn-sm">
                                        <?php echo translate('view_details', $current_language); ?>
                                    </a>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <?php echo translate('add_to_cart', $current_language); ?>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-primary btn-sm" disabled>
                                            <?php echo translate('out_of_stock', $current_language); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination" style="text-align: center; margin-top: 40px;">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=1<?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>" 
                               class="btn btn-secondary btn-sm">« <?php echo translate('first', $current_language); ?></a>
                            <a href="?page=<?php echo $current_page - 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>" 
                               class="btn btn-secondary btn-sm">‹ <?php echo translate('previous', $current_language); ?></a>
                        <?php endif; ?>

                        <span style="margin: 0 10px;">
                            <?php echo translate('page', $current_language); ?> <?php echo $current_page; ?> 
                            <?php echo translate('of', $current_language); ?> <?php echo $total_pages; ?>
                        </span>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>" 
                               class="btn btn-secondary btn-sm"><?php echo translate('next', $current_language); ?> ›</a>
                            <a href="?page=<?php echo $total_pages; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>" 
                               class="btn btn-secondary btn-sm"><?php echo translate('last', $current_language); ?> »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../js/localization.js"></script>
    <script src="../js/main.js"></script>
    <script>
        function filterByCategory(categoryId) {
            if (categoryId) {
                window.location.href = '?category=' + categoryId;
            } else {
                window.location.href = '?';
            }
        }

        function sortProducts(sortBy) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sortBy);
            window.location.href = '?' + params.toString();
        }

        function addToCart(productId) {
            fetch('../api/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('<?php echo translate('product_added_to_cart', $current_language); ?>', 'success');
                    updateCartCount();
                } else {
                    showNotification(data.message || '<?php echo translate('error_adding_to_cart', $current_language); ?>', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('<?php echo translate('error_adding_to_cart', $current_language); ?>', 'error');
            });
        }
    </script>
</body>
</html>
