<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/cart.php';

header('Content-Type: application/json');

$count = isset($_SESSION['shopping_cart']) ? Cart::get_cart_count() : 0;
echo json_encode(['count' => $count]);
?>
