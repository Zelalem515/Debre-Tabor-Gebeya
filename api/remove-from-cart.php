<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/cart.php';

header('Content-Type: application/json');

Auth::require_login();

$product_id = $_POST['product_id'] ?? 0;

$result = Cart::remove_from_cart($product_id);
echo json_encode($result);
?>
