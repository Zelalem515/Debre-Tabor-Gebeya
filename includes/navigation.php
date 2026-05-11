<?php
/**
 * Navigation Component
 */

$is_logged_in = Auth::is_logged_in();
$user_role = $is_logged_in ? Auth::get_user_role() : null;
$user_name = $is_logged_in ? Auth::get_user_name() : null;
$current_language = get_language();
?>

<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a href="<?php echo SITE_URL; ?>index.php">
                <span class="brand-name">DEBRETABOR GEBEYA</span>
                <span class="brand-amharic">ደብረታቦር ገበያ</span>
            </a>
        </div>
        
        <button class="navbar-toggle" id="navbar-toggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <div class="navbar-menu" id="navbar-menu">
            <ul class="navbar-nav">
                <?php if (!$is_logged_in): ?>
                    <li><a href="<?php echo SITE_URL; ?>customer/products.php"><?php echo translate('browse_products', $current_language); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>login.php"><?php echo translate('login', $current_language); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>register.php"><?php echo translate('register', $current_language); ?></a></li>
                <?php else: ?>
                    <?php if ($user_role === 'customer'): ?>
                        <li><a href="<?php echo SITE_URL; ?>customer/products.php"><?php echo translate('browse_products', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>customer/cart.php"><?php echo translate('cart', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>customer/orders.php"><?php echo translate('my_orders', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>customer/messages.php">💬 <?php echo translate('messages', $current_language); ?></a></li>
                    <?php elseif ($user_role === 'seller'): ?>
                        <li><a href="<?php echo SITE_URL; ?>seller/dashboard.php"><?php echo translate('dashboard', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>seller/products.php"><?php echo translate('products', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>seller/orders.php"><?php echo translate('orders', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>seller/chat.php">💬 <?php echo translate('messages', $current_language); ?></a></li>
                    <?php elseif ($user_role === 'admin'): ?>
                        <li><a href="<?php echo SITE_URL; ?>admin/dashboard.php"><?php echo translate('dashboard', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>admin/users.php"><?php echo translate('users', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>admin/categories.php"><?php echo translate('categories', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>admin/orders.php"><?php echo translate('orders', $current_language); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>admin/reports.php"><?php echo translate('reports', $current_language); ?></a></li>
                    <?php endif; ?>
                    
                    <li class="navbar-user">
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <a href="<?php echo SITE_URL; ?>logout.php"><?php echo translate('logout', $current_language); ?></a>
                    </li>
                <?php endif; ?>
                
                <li class="navbar-language">
                    <select id="language-selector" onchange="changeLanguage(this.value)">
                        <option value="en" <?php echo $current_language === 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="am" <?php echo $current_language === 'am' ? 'selected' : ''; ?>>አማርኛ</option>
                    </select>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
    // Toggle mobile menu
    document.getElementById('navbar-toggle').addEventListener('click', function() {
        document.getElementById('navbar-menu').classList.toggle('active');
    });
    
    // Change language
    function changeLanguage(lang) {
        console.log('changeLanguage called with:', lang);
        
        // Try multiple paths to find the correct API endpoint
        const paths = [
            '/lalibela-gebeya/api/set-language.php',
            '../api/set-language.php',
            '../../api/set-language.php',
            '/api/set-language.php'
        ];
        
        let attemptedPath = null;
        
        // Try the first path (absolute from root)
        fetch(paths[0], {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'language=' + lang
        }).then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (response.ok) {
                console.log('Language change successful, reloading page');
                // Reload page to apply language change
                setTimeout(() => {
                    location.reload();
                }, 100);
            } else {
                console.error('Failed to change language. Status:', response.status);
                // Try alternative paths
                tryAlternativePath(1);
            }
        }).catch(error => {
            console.error('Error with path ' + paths[0] + ':', error);
            // Try alternative paths
            tryAlternativePath(1);
        });
        
        function tryAlternativePath(index) {
            if (index >= paths.length) {
                console.error('All paths failed');
                alert('Failed to change language. Please try again.');
                return;
            }
            
            console.log('Trying alternative path:', paths[index]);
            
            fetch(paths[index], {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'language=' + lang
            }).then(response => {
                console.log('Response status for path ' + index + ':', response.status);
                
                if (response.ok) {
                    console.log('Language change successful with path:', paths[index]);
                    setTimeout(() => {
                        location.reload();
                    }, 100);
                } else {
                    tryAlternativePath(index + 1);
                }
            }).catch(error => {
                console.error('Error with path ' + paths[index] + ':', error);
                tryAlternativePath(index + 1);
            });
        }
    }
</script>
