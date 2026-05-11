<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/products.php';

header('Content-Type: application/json');

Auth::require_role('seller');

$product_id = $_POST['product_id'] ?? 0;
$seller_id = Auth::get_user_id();

$result = Products::delete_product($product_id, $seller_id);
echo json_encode($result);
?>
