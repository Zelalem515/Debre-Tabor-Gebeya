<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Seller Orders Page
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/orders.php';
require_once __DIR__ . '/../php/localization.php';

// Require seller login
Auth::require_role('seller');

$current_language = get_language();
$seller_id = $_SESSION['user_id'];

// Get seller's orders with customer names
$seller_orders_raw = Orders::get_seller_orders($seller_id);
$orders = [];

// Enrich orders with customer information
foreach ($seller_orders_raw as $order) {
    $customer = Database::fetch_one(
        "SELECT full_name FROM users WHERE id = ?",
        [$order['customer_id']]
    );
    $order['customer_name'] = $customer['full_name'] ?? 'Unknown';
    $order['order_id'] = $order['id'];
    $orders[] = $order;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    // Verify the order belongs to this seller
    $order = Orders::get_order_by_id($order_id);
    if ($order) {
        // Check if seller has items in this order
        $seller_items = Database::fetch_all(
            "SELECT COUNT(*) as count FROM order_items WHERE order_id = ? AND seller_id = ?",
            [$order_id, $seller_id]
        );
        
        if ($seller_items && $seller_items[0]['count'] > 0) {
            Orders::update_order_status($order_id, $new_status);
            $success_message = 'Order status updated successfully';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('my_orders', $current_language) ?? 'My Orders'; ?> - DEBRE TABOR GEBEYA</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navigation.css">
    <link rel="stylesheet" href="../css/auth.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .orders-table thead {
            background: #2c3e50;
            color: white;
        }
        
        .orders-table th {
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table tbody tr:hover {
            background: #f9f9f9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-paid {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-shipped {
            background: #cfe2ff;
            color: #084298;
        }
        
        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-small {
            padding: 8px 12px;
            font-size: 0.9rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-view {
            background: #f39c12;
            color: white;
        }
        
        .btn-view:hover {
            background: #e67e22;
        }
        
        .btn-update {
            background: #27ae60;
            color: white;
        }
        
        .btn-update:hover {
            background: #229954;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 400px;
            border-radius: 8px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../includes/navigation.php'; ?>

    <div class="orders-container">
        <h1><?php echo translate('my_orders', $current_language) ?? 'My Orders'; ?></h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <p><?php echo translate('no_orders', $current_language) ?? 'No orders found'; ?></p>
                <a href="products.php" class="btn btn-primary" style="margin-top: 20px;">
                    <?php echo translate('back_to_products', $current_language) ?? 'Back to Products'; ?>
                </a>
            </div>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th><?php echo translate('order_id', $current_language) ?? 'Order ID'; ?></th>
                        <th><?php echo translate('customer', $current_language) ?? 'Customer'; ?></th>
                        <th><?php echo translate('order_date', $current_language) ?? 'Order Date'; ?></th>
                        <th><?php echo translate('total_amount', $current_language) ?? 'Total Amount'; ?></th>
                        <th><?php echo translate('status', $current_language) ?? 'Status'; ?></th>
                        <th><?php echo translate('actions', $current_language) ?? 'Actions'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            <td><?php echo number_format($order['total_amount'], 2); ?> <?php echo CURRENCY_SYMBOL; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="order-detail.php?id=<?php echo $order['order_id']; ?>" class="btn-small btn-view">
                                        <?php echo translate('view_details', $current_language) ?? 'View'; ?>
                                    </a>
                                    <button class="btn-small btn-update" onclick="openStatusModal(<?php echo $order['order_id']; ?>, '<?php echo $order['status']; ?>')">
                                        <?php echo translate('update_status', $current_language) ?? 'Update'; ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeStatusModal()">&times;</span>
            <h2><?php echo translate('update_order_status', $current_language) ?? 'Update Order Status'; ?></h2>
            <form method="POST" id="statusForm">
                <input type="hidden" id="orderId" name="order_id">
                <div style="margin-bottom: 15px;">
                    <label for="statusSelect" style="display: block; margin-bottom: 5px; font-weight: bold;">
                        <?php echo translate('new_status', $current_language) ?? 'New Status'; ?>
                    </label>
                    <select id="statusSelect" name="status" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="Pending"><?php echo translate('pending', $current_language) ?? 'Pending'; ?></option>
                        <option value="Paid"><?php echo translate('paid', $current_language) ?? 'Paid'; ?></option>
                        <option value="Shipped"><?php echo translate('shipped', $current_language) ?? 'Shipped'; ?></option>
                        <option value="Delivered"><?php echo translate('delivered', $current_language) ?? 'Delivered'; ?></option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-small" onclick="closeStatusModal()" style="background: #ccc; color: #333;">
                        <?php echo translate('cancel', $current_language) ?? 'Cancel'; ?>
                    </button>
                    <button type="submit" class="btn-small btn-update">
                        <?php echo translate('update', $current_language) ?? 'Update'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
        function openStatusModal(orderId, currentStatus) {
            document.getElementById('orderId').value = orderId;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
