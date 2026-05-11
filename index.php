<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Main Entry Point
 */

session_start();
require_once 'config.php';
require_once 'php/localization.php';

// Get current language FIRST before any redirects
$current_language = get_language();

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : null;

// Redirect based on user role
if ($is_logged_in) {
    switch ($user_role) {
        case 'admin':
            header('Location: admin/dashboard.php');
            exit;
        case 'seller':
            header('Location: seller/dashboard.php');
            exit;
        case 'customer':
            header('Location: customer/products.php');
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEBRETABOR GEBEYA - Ethiopian Online Marketplace</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .brand-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #f39c12;
        }
        
        .navbar-nav {
            list-style: none;
            display: flex;
            gap: 2rem;
        }
        
        .navbar-nav a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .navbar-nav a:hover {
            color: #f39c12;
        }
        
        .hero {
            position: relative;
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            min-height: 800px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(135deg, #1a472a 0%, #2d5a3d 100%);
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(26, 71, 42, 0.7) 0%, rgba(45, 90, 61, 0.5) 100%);
            z-index: 1;
        }
        
        .hero-carousel {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            overflow: hidden;
        }
        
        .carousel-image {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            opacity: 0;
            animation: smoothFade 5s ease-in-out infinite;
        }
        
        .carousel-image:nth-child(1) { animation-delay: 0s; }
        .carousel-image:nth-child(2) { animation-delay: 5s; }
        .carousel-image:nth-child(3) { animation-delay: 10s; }
        .carousel-image:nth-child(4) { animation-delay: 15s; }
        .carousel-image:nth-child(5) { animation-delay: 20s; }
        .carousel-image:nth-child(6) { animation-delay: 25s; }
        .carousel-image:nth-child(7) { animation-delay: 30s; }
        .carousel-image:nth-child(8) { animation-delay: 35s; }
        .carousel-image:nth-child(9) { animation-delay: 40s; }
        .carousel-image:nth-child(10) { animation-delay: 45s; }
        .carousel-image:nth-child(11) { animation-delay: 50s; }
        
        @keyframes smoothFade {
            0% { 
                opacity: 0;
            }
            10% { 
                opacity: 1;
            }
            90% { 
                opacity: 1;
            }
            100% { 
                opacity: 0;
            }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            animation: slideDown 0.8s ease-out;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.8s ease-out 0.2s both;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: slideUp 0.8s ease-out 0.4s both;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-primary {
            background: #f39c12;
            color: white;
        }
        
        .btn-primary:hover {
            background: #e67e22;
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
        }
        
        .btn-secondary:hover {
            background: #f0f0f0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .featured-products {
            padding: 3rem 2rem;
            background: white;
            margin: 2rem 0;
        }
        
        .featured-products h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .product-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        
        .product-info {
            padding: 1rem;
        }
        
        .product-name {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .product-price {
            color: #f39c12;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .language-selector {
            background: #34495e;
            padding: 0.5rem;
            border-radius: 4px;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="brand-name"><?php echo translate('site_name', $current_language); ?></div>
            <ul class="navbar-nav">
                <li><a href="customer/products.php"><?php echo translate('browse_products', $current_language); ?></a></li>
                <li><a href="login.php"><?php echo translate('login', $current_language); ?></a></li>
                <li><a href="register.php"><?php echo translate('register', $current_language); ?></a></li>
                <li>
                    <select class="language-selector" onchange="changeLanguage(this.value)">
                        <option value="en" <?php echo $current_language === 'en' ? 'selected' : ''; ?>><?php echo translate('english', $current_language); ?></option>
                        <option value="am" <?php echo $current_language === 'am' ? 'selected' : ''; ?>><?php echo translate('amharic', $current_language); ?></option>
                    </select>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section with Animated Background -->
    <section class="hero">
        <div class="hero-carousel">
            <img src="home.png" alt="Home" class="carousel-image">
            <img src="home1.png" alt="Home 1" class="carousel-image">
            <img src="home2.png" alt="Home 2" class="carousel-image">
            <img src="home3.png" alt="Home 3" class="carousel-image">
            <img src="home4.png" alt="Home 4" class="carousel-image">
            <img src="customer.png" alt="Customer" class="carousel-image">
            <img src="deliver.png" alt="Delivery" class="carousel-image">
            <img src="feedback.png" alt="Feedback" class="carousel-image">
            <img src="payment.png" alt="Payment" class="carousel-image">
            <img src="track.png" alt="Track" class="carousel-image">
            <img src="track1.png" alt="Track 1" class="carousel-image">
        </div>
        <div class="hero-content">
            <h1><?php echo translate('welcome_to', $current_language); ?> <?php echo translate('site_name', $current_language); ?></h1>
            <p><?php echo translate('hero_subtitle', $current_language); ?></p>
            <div class="hero-buttons">
                <a href="customer/products.php" class="btn btn-primary"><?php echo translate('browse_products', $current_language); ?></a>
                <a href="login.php" class="btn btn-secondary"><?php echo translate('login', $current_language); ?></a>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products">
        <div class="container">
            <h2><?php echo translate('featured_products', $current_language); ?></h2>
            <div class="products-grid" id="featured-products">
                <div class="product-card">
                    <div class="product-image">📱</div>
                    <div class="product-info">
                        <div class="product-name">Electronics</div>
                        <div class="product-price">From 500 Br</div>
                        <a href="customer/products.php" class="btn btn-primary" style="width: 100%; text-align: center;"><?php echo translate('view_details', $current_language); ?></a>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-image">👕</div>
                    <div class="product-info">
                        <div class="product-name">Clothing</div>
                        <div class="product-price">From 200 Br</div>
                        <a href="customer/products.php" class="btn btn-primary" style="width: 100%; text-align: center;"><?php echo translate('view_details', $current_language); ?></a>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-image">📚</div>
                    <div class="product-info">
                        <div class="product-name">Books</div>
                        <div class="product-price">From 100 Br</div>
                        <a href="customer/products.php" class="btn btn-primary" style="width: 100%; text-align: center;"><?php echo translate('view_details', $current_language); ?></a>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-image">🏠</div>
                    <div class="product-info">
                        <div class="product-name">Home & Garden</div>
                        <div class="product-price">From 300 Br</div>
                        <a href="customer/products.php" class="btn btn-primary" style="width: 100%; text-align: center;"><?php echo translate('view_details', $current_language); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 <?php echo translate('site_name', $current_language); ?>. <?php echo translate('all_rights_reserved', $current_language) ?? 'All rights reserved.'; ?></p>
        <p><?php echo translate('ethiopian_ecommerce_excellence', $current_language) ?? 'Ethiopian E-Commerce Excellence'; ?></p>
    </footer>

    <script>
        function changeLanguage(lang) {
            console.log('changeLanguage called with:', lang);
            
            // Try multiple paths to find the correct API endpoint
            const paths = [
                '/lalibela-gebeya/api/set-language.php',
                'api/set-language.php',
                '../api/set-language.php'
            ];
            
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
</body>
</html>
