<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/cart.php';

header('Content-Type: application/json');

Auth::require_login();

$product_id = $_POST['product_id'] ?? 0;
$change = $_POST['change'] ?? 0;

$cart = Cart::get_cart();
$current_quantity = $cart[$product_id]['quantity'] ?? 0;
$new_quantity = $current_quantity + $change;

$result = Cart::update_cart_item($product_id, $new_quantity);
echo json_encode($result);
?>
