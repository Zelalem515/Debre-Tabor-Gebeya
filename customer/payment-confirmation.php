<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Payment Confirmation Page
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/payment.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/localization.php';

// Require customer login
Auth::require_role('customer');

$current_language = get_language();

// Check if checkout data exists
if (!isset($_SESSION['checkout_data'])) {
    header('Location: checkout.php');
    exit;
}

$checkout_data = $_SESSION['checkout_data'];
$cart = $checkout_data['cart'];
$total_amount = $checkout_data['total_amount'];
$payment_method = $checkout_data['payment_method'];
$shipping_address = $checkout_data['shipping_address'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate payment method credentials
    $payment_credentials = [];
    $validation_error = null;
    
    if ($payment_method === 'Telebirr') {
        $phone = $_POST['telebirr_phone'] ?? '';
        $password = $_POST['telebirr_password'] ?? '';
        
        if (empty($phone) || empty($password)) {
            $validation_error = 'Phone number and password are required for Telebirr';
        } elseif (!preg_match('/^\+?251[0-9]{9}$/', $phone)) {
            $validation_error = 'Invalid phone number format';
        } else {
            $payment_credentials = [
                'phone' => $phone,
                'password' => $password
            ];
        }
    } elseif ($payment_method === 'CBE') {
        $account = $_POST['cbe_account'] ?? '';
        $pin = $_POST['cbe_pin'] ?? '';
        
        if (empty($account) || empty($pin)) {
            $validation_error = 'Account number and PIN are required for CBE';
        } elseif (!preg_match('/^[0-9]{10,16}$/', $account)) {
            $validation_error = 'Invalid account number format';
        } elseif (!preg_match('/^[0-9]{4}$/', $pin)) {
            $validation_error = 'PIN must be 4 digits';
        } else {
            $payment_credentials = [
                'account_number' => $account,
                'pin' => $pin
            ];
        }
    } elseif ($payment_method === 'BOA') {
        $account = $_POST['boa_account'] ?? '';
        $pin = $_POST['boa_pin'] ?? '';
        
        if (empty($account) || empty($pin)) {
            $validation_error = 'Account number and PIN are required for BOA';
        } elseif (!preg_match('/^[0-9]{10,16}$/', $account)) {
            $validation_error = 'Invalid account number format';
        } elseif (!preg_match('/^[0-9]{4}$/', $pin)) {
            $validation_error = 'PIN must be 4 digits';
        } else {
            $payment_credentials = [
                'account_number' => $account,
                'pin' => $pin
            ];
        }
    }
    
    if ($validation_error) {
        $error_message = $validation_error;
    } else {
        // Create order
        $order_result = Orders::create_order(
            $checkout_data['customer_id'],
            $cart,
            $shipping_address,
            $payment_method
        );
        
        if ($order_result['success']) {
            $order_id = $order_result['order_id'];
            
            // Process payment with credentials
            $payment_result = Payment::process_payment($order_id, $payment_method, $payment_credentials);
            
            // Store payment result in session
            $_SESSION['payment_result'] = $payment_result;
            $_SESSION['order_id'] = $order_id;
            
            // Clear checkout data
            unset($_SESSION['checkout_data']);
            
            // Redirect to payment result page
            header('Location: payment-result.php');
            exit;
        } else {
            $error_message = $order_result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('payment_confirmation', $current_language); ?> - DEBRETABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="container" style="padding: 40px 20px;">
        <h1><?php echo translate('payment_confirmation', $current_language); ?></h1>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <a href="checkout.php" class="btn btn-secondary">
                <?php echo translate('back_to_checkout', $current_language); ?>
            </a>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Payment Details -->
                <div>
                    <h2><?php echo translate('payment_details', $current_language); ?></h2>
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
                        <div style="margin-bottom: 20px;">
                            <p><strong><?php echo translate('payment_method', $current_language); ?>:</strong></p>
                            <p style="font-size: 1.2rem; color: var(--primary-color);">
                                <?php echo htmlspecialchars($payment_method); ?>
                            </p>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <p><strong><?php echo translate('shipping_address', $current_language); ?>:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($shipping_address)); ?></p>
                        </div>

                        <hr>

                        <div style="margin-bottom: 20px;">
                            <p><strong><?php echo translate('order_total', $current_language); ?>:</strong></p>
                            <p style="font-size: 1.5rem; font-weight: bold; color: var(--secondary-color);">
                                <?php echo number_format($total_amount, 2); ?> <?php echo CURRENCY_SYMBOL; ?>
                            </p>
                        </div>

                        <div style="background: #e8f4f8; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                            <p style="margin: 0; color: #0c5460;">
                                <strong><?php echo translate('payment_info', $current_language); ?>:</strong><br>
                                <?php echo translate('payment_simulation_message', $current_language); ?>
                            </p>
                        </div>

                        <!-- Payment Method Specific Forms -->
                        <form method="POST" id="payment-form">
                            <?php if ($payment_method === 'Telebirr'): ?>
                                <div style="background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                                    <h3 style="margin-top: 0; color: #856404;">Telebirr Payment</h3>
                                    
                                    <div style="margin-bottom: 15px;">
                                        <label for="telebirr_phone" style="display: block; margin-bottom: 5px; font-weight: bold;">
                                            <?php echo translate('phone_number', $current_language) ?? 'Phone Number'; ?>
                                        </label>
                                        <input type="tel" id="telebirr_phone" name="telebirr_phone" 
                                               placeholder="+251 9XX XXX XXX" 
                                               pattern="^\+?251[0-9]{9}$"
                                               required
                                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                        <small style="color: #666;">Format: +251 followed by 9 digits</small>
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <label for="telebirr_password" style="display: block; margin-bottom: 5px; font-weight: bold;">
                                            <?php echo translate('password', $current_language) ?? 'Password'; ?>
                                        </label>
                                        <input type="password" id="telebirr_password" name="telebirr_password" 
                                               placeholder="Enter your Telebirr password"
                                               required
                                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                    </div>
                                </div>

                            <?php elseif ($payment_method === 'CBE'): ?>
                                <div style="background: #d1ecf1; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #17a2b8;">
                                    <h3 style="margin-top: 0; color: #0c5460;">Commercial Bank of Ethiopia (CBE)</h3>
                                    
                                    <div style="margin-bottom: 15px;">
                                        <label for="cbe_account" style="display: block; margin-bottom: 5px; font-weight: bold;">
                                            <?php echo translate('account_number', $current_language) ?? 'Account Number'; ?>
                                        </label>
                                        <input type="text" id="cbe_account" name="cbe_account" 
                                               placeholder="Enter your CBE account number"
                                               pattern="^[0-9]{10,16}$"
                                               required
                                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                        <small style="color: #666;">10-16 digits</small>
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <label for="cbe_pin" style="display: block; margin-bottom: 5px; font-weight: bold;">
                                            <?php echo translate('pin', $current_language) ?? 'PIN'; ?>
                                        </label>
                                        <input type="password" id="cbe_pin" name="cbe_pin" 
                                               placeholder="Enter your 4-digit PIN"
                                               pattern="^[0-9]{4}$"
                                               required
                                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                        <small style="color: #666;">4 digits</small>
                                    </div>
                                </div>

                            <?php elseif ($payment_method === 'BOA'): ?>
                                <div style="background: #d4edda; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                                    <h3 style="margin-top: 0; color: #155724;">Bank of Abyssinia (BOA)</h3>
                                    
                                    <div style="margin-bottom: 15px;">
                                        <label for="boa_account" style="display: block; margin-bottom: 5px; font-weight: bold;">
                                            <?php echo translate('account_number', $current_language) ?? 'Account Number'; ?>
                                        </label>
                                        <input type="text" id="boa_account" name="boa_account" 
                                               placeholder="Enter your BOA account number"
                                               pattern="^[0-9]{10,16}$"
                                               required
                                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                        <small style="color: #666;">10-16 digits</small>
                                    </div>

                                    <div style="margin-bottom: 15px;">
                                        <label for="boa_pin" style="display: block; margin-bottom: 5px; font-weight: bold;">
                                            <?php echo translate('pin', $current_language) ?? 'PIN'; ?>
                                        </label>
                                        <input type="password" id="boa_pin" name="boa_pin" 
                                               placeholder="Enter your 4-digit PIN"
                                               pattern="^[0-9]{4}$"
                                               required
                                               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                        <small style="color: #666;">4 digits</small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary btn-block" style="margin-bottom: 10px;">
                                <?php echo translate('confirm_and_pay', $current_language); ?>
                            </button>
                            <a href="checkout.php" class="btn btn-secondary btn-block">
                                <?php echo translate('back_to_checkout', $current_language); ?>
                            </a>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div>
                    <h2><?php echo translate('order_summary', $current_language); ?></h2>
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
                        <table style="width: 100%; margin-bottom: 20px;">
                            <thead>
                                <tr>
                                    <th><?php echo translate('product', $current_language); ?></th>
                                    <th><?php echo translate('qty', $current_language); ?></th>
                                    <th><?php echo translate('price', $current_language); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart as $product_id => $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(substr($item['name'], 0, 20)); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <hr>

                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                            <span><?php echo translate('total', $current_language); ?>:</span>
                            <span><?php echo number_format($total_amount, 2); ?> <?php echo CURRENCY_SYMBOL; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="../js/localization.js"></script>
</body>
</html>
