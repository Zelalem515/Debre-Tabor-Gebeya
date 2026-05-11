<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * User Login Page
 */

session_start();
require_once 'config.php';
require_once 'php/auth.php';
require_once 'php/localization.php';

// If already logged in, redirect to dashboard
if (Auth::is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Server-side validation
    if (empty($email) || empty($password)) {
        $error_message = translate('all_fields_required', get_language());
    } else {
        // Attempt login
        $result = Auth::login_user($email, $password);
        
        if ($result['success']) {
            $success_message = translate('login_successful', get_language());
            
            // Redirect based on user role
            $user_role = Auth::get_user_role();
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
        } else {
            $error_message = $result['message'];
        }
    }
}

$current_language = get_language();
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('login', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <div class="auth-container">
        <div class="auth-box">
            <h1><?php echo translate('login', $current_language); ?></h1>
            
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
            
            <form method="POST" id="login-form" novalidate>
                <div class="form-group">
                    <label for="email"><?php echo translate('email', $current_language); ?></label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <span class="error-message" id="email-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="password"><?php echo translate('password', $current_language); ?></label>
                    <input type="password" id="password" name="password" required>
                    <span class="error-message" id="password-error"></span>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <?php echo translate('login', $current_language); ?>
                </button>
            </form>
            
            <p class="auth-link">
                <?php echo translate('dont_have_account', $current_language); ?>
                <a href="register.php"><?php echo translate('register', $current_language); ?></a>
            </p>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
            
            
        </div>
    </div>

    <script src="js/localization.js"></script>
    <script src="js/validation.js"></script>
    <script>
        // Client-side validation
        document.getElementById('login-form').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            if (!validateEmail(email)) {
                document.getElementById('email-error').textContent = 'Invalid email format';
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            if (password.length === 0) {
                document.getElementById('password-error').textContent = 'Password is required';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
