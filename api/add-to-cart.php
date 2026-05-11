<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/cart.php';

header('Content-Type: application/json');

Auth::require_login();

$product_id = $_POST['product_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;

$result = Cart::add_to_cart($product_id, $quantity);
echo json_encode($result);
?>
