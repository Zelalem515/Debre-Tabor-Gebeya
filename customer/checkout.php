<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Checkout Payment Page
 * 
 * Allows customers to select payment method and enter credentials
 * Supports: Telebirr, CBE Birr, BOA, Cash on Delivery
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/payment.php';
require_once __DIR__ . '/../php/localization.php';

session_start();
$current_language = get_language();

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get order ID
$order_id = $_GET['order_id'] ?? $_SESSION['current_order_id'] ?? null;

// If no order ID, create one from cart
if (!$order_id && isset($_SESSION['user_id'])) {
    $cart = $_SESSION['shopping_cart'] ?? [];
    
    if (!empty($cart)) {
        $items = [];
        foreach ($cart as $product_id => $item) {
            $items[] = [
                'product_id' => $product_id,
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }
        
        $order_result = Orders::create_order(
            $_SESSION['user_id'],
            $items,
            'Pending Address',
            'Pending Method'
        );
        
        if ($order_result['success']) {
            $order_id = $order_result['order_id'];
            $_SESSION['current_order_id'] = $order_id;
        } else {
            die('Error: ' . $order_result['message']);
        }
    } else {
        die('Error: Cart is empty');
    }
}

if (!$order_id) {
    die('Error: Order ID is required');
}

// Get order details
$order = Orders::get_order_by_id($order_id);

if (!$order) {
    die('Error: Order not found');
}

// Handle payment submission
$payment_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $delivery_address = $_POST['delivery_address'] ?? '';
    
    // Validate delivery address
    if (empty($delivery_address) || strlen(trim($delivery_address)) < 5) {
        $payment_error = 'Please provide a complete delivery address (at least 5 characters)';
    } elseif (!$payment_method) {
        $payment_error = 'Please select a payment method';
    } elseif (!in_array($payment_method, PAYMENT_METHODS)) {
        $payment_error = 'Invalid payment method selected';
    } else {
        // Update order with delivery address and payment method
        Database::execute_query(
            "UPDATE orders SET delivery_address = ?, payment_method = ? WHERE id = ?",
            [$delivery_address, $payment_method, $order_id]
        );
        
        // Prepare credentials
        $credentials = [];
        
        switch ($payment_method) {
            case 'Telebirr':
                $credentials['phone'] = $_POST['telebirr_phone'] ?? '';
                $credentials['password'] = $_POST['telebirr_password'] ?? '';
                if (empty($credentials['phone']) || empty($credentials['password'])) {
                    $payment_error = 'Phone number and password are required for Telebirr';
                }
                break;
                
            case 'CBE Birr':
                $credentials['account_number'] = $_POST['cbe_account'] ?? '';
                $credentials['pin'] = $_POST['cbe_pin'] ?? '';
                if (empty($credentials['account_number']) || empty($credentials['pin'])) {
                    $payment_error = 'Account number and PIN are required for CBE Birr';
                }
                break;
                
            case 'BOA':
                $credentials['account_number'] = $_POST['boa_account'] ?? '';
                $credentials['pin'] = $_POST['boa_pin'] ?? '';
                if (empty($credentials['account_number']) || empty($credentials['pin'])) {
                    $payment_error = 'Account number and PIN are required for BOA';
                }
                break;
                
            case 'Cash on Delivery':
                break;
        }
        
        // Process payment if no errors
        if (empty($payment_error)) {
            $payment_result = Payment::process_payment(
                $order_id,
                $payment_method,
                $credentials
            );
            
            if ($payment_result['success']) {
                $_SESSION['shopping_cart'] = [];
                header("Location: payment-success.php?order_id={$order_id}&status=success");
                exit;
            } else {
                $payment_error = $payment_result['message'] ?? 'Payment processing failed';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('payment', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .order-summary {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }
        
        .payment-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-total {
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
            border-top: 2px solid #eee;
            margin-top: 20px;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .payment-method-option {
            margin-bottom: 15px;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .payment-method-option input[type="radio"] {
            margin-right: 10px;
        }
        
        .payment-method-option label {
            cursor: pointer;
            display: flex;
            align-items: center;
            font-weight: 500;
            color: #333;
            margin: 0;
        }
        
        .payment-method-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .payment-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        
        .credentials-form {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .credentials-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>💳 <?php echo translate('payment', $current_language); ?></h1>
            <p style="color: #666;"><?php echo translate('order_id', $current_language); ?> #<?php echo htmlspecialchars($order_id); ?></p>
        </header>
        
        <div class="checkout-grid">
            <!-- Order Summary -->
            <div class="order-summary">
                <h2>📋 <?php echo translate('order_summary', $current_language); ?></h2>
                
                <div class="order-item">
                    <span><?php echo translate('order_id', $current_language); ?>:</span>
                    <span style="font-weight: 600;">#<?php echo htmlspecialchars($order_id); ?></span>
                </div>
                
                <div class="order-item">
                    <span><?php echo translate('status', $current_language); ?>:</span>
                    <span style="font-weight: 600;"><?php echo htmlspecialchars($order['status']); ?></span>
                </div>
                
                <div class="order-item">
                    <span><?php echo translate('delivery_address', $current_language); ?>:</span>
                    <span style="font-weight: 600; color: #d9534f;"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                </div>
                
                <div class="order-total">
                    <span><?php echo translate('total_amount', $current_language); ?>:</span>
                    <span><?php echo htmlspecialchars($order['total_amount']); ?> ETB</span>
                </div>
                
                <div class="info-box">
                    ℹ️ <?php echo translate('select_payment_method', $current_language); ?>
                </div>
            </div>
            
            <!-- Payment Section -->
            <div class="payment-section">
                <h2>💰 <?php echo translate('select_payment_method', $current_language); ?></h2>
                
                <?php if (!empty($payment_error)): ?>
                    <div class="error-box">
                        ❌ <?php echo htmlspecialchars($payment_error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="payment-form">
                    <div class="form-group">
                        <label for="delivery_address" style="font-weight: 600; color: #d9534f;">📍 <?php echo translate('delivery_address', $current_language); ?> *</label>
                        <textarea id="delivery_address" name="delivery_address" required placeholder="<?php echo translate('delivery_address', $current_language); ?>"><?php echo htmlspecialchars($order['delivery_address'] !== 'Pending Address' ? $order['delivery_address'] : ''); ?></textarea>
                        <small style="color: #666;"><?php echo translate('delivery_address', $current_language); ?></small>
                    </div>
                    
                    <!-- Telebirr -->
                    <div class="payment-method-option" onclick="selectPaymentMethod(this, 'telebirr')">
                        <label>
                            <input type="radio" name="payment_method" value="Telebirr">
                            <span class="payment-icon">📱</span>
                            <span><?php echo translate('telebirr', $current_language); ?></span>
                        </label>
                    </div>
                    <div id="telebirr" class="credentials-form">
                        <div class="form-group">
                            <label><?php echo translate('phone_number', $current_language); ?></label>
                            <input type="text" name="telebirr_phone" placeholder="0919407548">
                        </div>
                        <div class="form-group">
                            <label><?php echo translate('password', $current_language); ?></label>
                            <input type="password" name="telebirr_password" placeholder="<?php echo translate('password', $current_language); ?>">
                        </div>
                    </div>
                    
                    <!-- CBE Birr -->
                    <div class="payment-method-option" onclick="selectPaymentMethod(this, 'cbe')">
                        <label>
                            <input type="radio" name="payment_method" value="CBE Birr">
                            <span class="payment-icon">🏦</span>
                            <span><?php echo translate('cbe_birr', $current_language); ?></span>
                        </label>
                    </div>
                    <div id="cbe" class="credentials-form">
                        <div class="form-group">
                            <label><?php echo translate('account_number', $current_language); ?></label>
                            <input type="text" name="cbe_account" placeholder="<?php echo translate('account_number', $current_language); ?>">
                        </div>
                        <div class="form-group">
                            <label><?php echo translate('pin', $current_language); ?></label>
                            <input type="password" name="cbe_pin" placeholder="<?php echo translate('pin', $current_language); ?>">
                        </div>
                    </div>
                    
                    <!-- BOA -->
                    <div class="payment-method-option" onclick="selectPaymentMethod(this, 'boa')">
                        <label>
                            <input type="radio" name="payment_method" value="BOA">
                            <span class="payment-icon">🏦</span>
                            <span><?php echo translate('bank_of_abyssinia', $current_language); ?></span>
                        </label>
                    </div>
                    <div id="boa" class="credentials-form">
                        <div class="form-group">
                            <label><?php echo translate('account_number', $current_language); ?></label>
                            <input type="text" name="boa_account" placeholder="<?php echo translate('account_number', $current_language); ?>">
                        </div>
                        <div class="form-group">
                            <label><?php echo translate('pin', $current_language); ?></label>
                            <input type="password" name="boa_pin" placeholder="<?php echo translate('pin', $current_language); ?>">
                        </div>
                    </div>
                    
                    <!-- Cash on Delivery -->
                    <div class="payment-method-option" onclick="selectPaymentMethod(this, 'cod')">
                        <label>
                            <input type="radio" name="payment_method" value="Cash on Delivery">
                            <span class="payment-icon">💵</span>
                            <span><?php echo translate('cash_on_delivery', $current_language); ?></span>
                        </label>
                    </div>
                    <div id="cod" class="credentials-form">
                        <div class="info-box">
                            ℹ️ <?php echo translate('cash_on_delivery', $current_language); ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="button">✅ <?php echo translate('complete_payment', $current_language); ?></button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Select payment method and show credentials form
        function selectPaymentMethod(element, formId) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-method-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Check the radio button
            const radio = element.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
            
            // Hide all credential forms
            document.querySelectorAll('.credentials-form').forEach(form => {
                form.classList.remove('active');
            });
            
            // Show the selected form
            const form = document.getElementById(formId);
            if (form) {
                form.classList.add('active');
            }
        }
        
        // Show/hide credential forms based on payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Hide all forms
                document.querySelectorAll('.credentials-form').forEach(form => {
                    form.classList.remove('active');
                });
                
                // Show the selected form
                const methodValue = this.value;
                let formId = '';
                
                if (methodValue === 'Telebirr') formId = 'telebirr';
                else if (methodValue === 'CBE Birr') formId = 'cbe';
                else if (methodValue === 'BOA') formId = 'boa';
                else if (methodValue === 'Cash on Delivery') formId = 'cod';
                
                if (formId) {
                    const form = document.getElementById(formId);
                    if (form) form.classList.add('active');
                }
            });
        });
        
        // Form submission validation
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            const address = document.getElementById('delivery_address').value.trim();
            const method = document.querySelector('input[name="payment_method"]:checked');
            
            // Validate address
            if (!address || address.length < 5) {
                e.preventDefault();
                alert('<?php echo translate('delivery_address', $current_language); ?> - <?php echo translate('all_fields_required', $current_language); ?>');
                document.getElementById('delivery_address').focus();
                return false;
            }
            
            // Validate payment method is selected
            if (!method) {
                e.preventDefault();
                alert('<?php echo translate('select_payment_method', $current_language); ?>');
                return false;
            }
            
            // Validate credentials based on payment method
            const paymentMethod = method.value;
            
            if (paymentMethod === 'Telebirr') {
                const phone = document.querySelector('input[name="telebirr_phone"]').value.trim();
                const password = document.querySelector('input[name="telebirr_password"]').value.trim();
                
                if (!phone || !password) {
                    e.preventDefault();
                    alert('<?php echo translate('phone_number', $current_language); ?> & <?php echo translate('password', $current_language); ?> - <?php echo translate('all_fields_required', $current_language); ?>');
                    return false;
                }
            } else if (paymentMethod === 'CBE Birr') {
                const account = document.querySelector('input[name="cbe_account"]').value.trim();
                const pin = document.querySelector('input[name="cbe_pin"]').value.trim();
                
                if (!account || !pin) {
                    e.preventDefault();
                    alert('<?php echo translate('account_number', $current_language); ?> & <?php echo translate('pin', $current_language); ?> - <?php echo translate('all_fields_required', $current_language); ?>');
                    return false;
                }
            } else if (paymentMethod === 'BOA') {
                const account = document.querySelector('input[name="boa_account"]').value.trim();
                const pin = document.querySelector('input[name="boa_pin"]').value.trim();
                
                if (!account || !pin) {
                    e.preventDefault();
                    alert('<?php echo translate('account_number', $current_language); ?> & <?php echo translate('pin', $current_language); ?> - <?php echo translate('all_fields_required', $current_language); ?>');
                    return false;
                }
            }
            
            return true;
        });
    </script>
</body>
</html>
