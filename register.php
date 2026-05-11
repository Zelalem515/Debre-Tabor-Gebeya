<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * User Registration Page
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
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $profile_picture = null;
    
    // Server-side validation
    if (empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error_message = translate('all_fields_required', get_language());
    } elseif ($password !== $confirm_password) {
        $error_message = translate('passwords_do_not_match', get_language());
    } else {
        // Handle profile picture upload for sellers
        if ($role === 'seller' && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            
            // Validate file
            $file_size = $file['size'];
            $file_type = $file['type'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            
            // Check file size (5MB max)
            if ($file_size > MAX_FILE_SIZE) {
                $error_message = 'Profile picture file size must not exceed 5MB';
            } else {
                // Check file type
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($file_type, $allowed_types)) {
                    $error_message = 'Only JPG, PNG, and GIF files are allowed for profile picture';
                } else {
                    // Generate unique filename
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $unique_filename = 'profile_' . time() . '_' . uniqid() . '.' . $file_ext;
                    $upload_path = UPLOAD_DIR . $unique_filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $profile_picture = 'images/uploads/' . $unique_filename;
                    } else {
                        $error_message = 'Failed to upload profile picture. Please try again.';
                    }
                }
            }
        }
        
        // If no error from file upload, proceed with registration
        if (empty($error_message)) {
            // Attempt registration
            $result = Auth::register_user($email, $password, $full_name, $role, $profile_picture);
            
            if ($result['success']) {
                $success_message = translate('registration_successful', get_language());
                // Redirect to login after 2 seconds
                header('Refresh: 2; url=login.php');
            } else {
                $error_message = $result['message'];
            }
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
    <title><?php echo translate('register', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <div class="auth-container">
        <div class="auth-box">
            <h1><?php echo translate('register', $current_language); ?></h1>
            
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
            
            <form method="POST" id="registration-form" enctype="multipart/form-data" novalidate>
                <div class="form-group">
                    <label for="full_name"><?php echo translate('full_name', $current_language); ?></label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    <span class="error-message" id="full_name-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="email"><?php echo translate('email', $current_language); ?></label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <span class="error-message" id="email-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="password"><?php echo translate('password', $current_language); ?></label>
                    <input type="password" id="password" name="password" required>
                    <small><?php echo translate('password_requirements', $current_language); ?></small>
                    <span class="error-message" id="password-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><?php echo translate('confirm_password', $current_language); ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span class="error-message" id="confirm_password-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="role"><?php echo translate('account_type', $current_language); ?></label>
                    <select id="role" name="role" required onchange="toggleProfilePicture()">
                        <option value="customer"><?php echo translate('customer', $current_language); ?></option>
                        <option value="seller"><?php echo translate('seller', $current_language); ?></option>
                    </select>
                </div>
                
                <!-- Profile Picture (for sellers) -->
                <div class="form-group" id="profile-picture-group" style="display: none;">
                    <label for="profile_picture">📸 Profile Picture (Seller)</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    <small>Optional: Upload a profile picture (JPG, PNG, GIF - Max 5MB)</small>
                    <span class="error-message" id="profile_picture-error"></span>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <?php echo translate('register', $current_language); ?>
                </button>
            </form>
            
            <p class="auth-link">
                <?php echo translate('already_have_account', $current_language); ?>
                <a href="login.php"><?php echo translate('login', $current_language); ?></a>
            </p>
        </div>
    </div>

    <script src="js/localization.js"></script>
    <script src="js/validation.js"></script>
    <script>
        // Toggle profile picture field based on role selection
        function toggleProfilePicture() {
            const role = document.getElementById('role').value;
            const profilePictureGroup = document.getElementById('profile-picture-group');
            
            if (role === 'seller') {
                profilePictureGroup.style.display = 'block';
            } else {
                profilePictureGroup.style.display = 'none';
                // Clear file input if switching away from seller
                document.getElementById('profile_picture').value = '';
            }
        }
        
        // Client-side validation
        document.getElementById('registration-form').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            
            // Validate full name
            const fullName = document.getElementById('full_name').value.trim();
            if (fullName.length < 2) {
                document.getElementById('full_name-error').textContent = 'Name must be at least 2 characters';
                isValid = false;
            }
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            if (!validateEmail(email)) {
                document.getElementById('email-error').textContent = 'Invalid email format';
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            if (!validatePassword(password)) {
                document.getElementById('password-error').textContent = 'Password must be at least 8 characters with uppercase, lowercase, and numbers';
                isValid = false;
            }
            
            // Validate confirm password
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                document.getElementById('confirm_password-error').textContent = 'Passwords do not match';
                isValid = false;
            }
            
            // Validate profile picture if seller
            const role = document.getElementById('role').value;
            if (role === 'seller') {
                const profilePicture = document.getElementById('profile_picture').files[0];
                if (profilePicture) {
                    // Check file size (5MB max)
                    if (profilePicture.size > 5242880) {
                        document.getElementById('profile_picture-error').textContent = 'File size must not exceed 5MB';
                        isValid = false;
                    }
                    // Check file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(profilePicture.type)) {
                        document.getElementById('profile_picture-error').textContent = 'Only JPG, PNG, and GIF files are allowed';
                        isValid = false;
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        function validatePassword(password) {
            if (password.length < 8) return false;
            if (!/[A-Z]/.test(password)) return false;
            if (!/[a-z]/.test(password)) return false;
            if (!/[0-9]/.test(password)) return false;
            return true;
        }
    </script>
</body>
</html>
