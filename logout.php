<?php
/**
 * DEBRE TABOR GEBEYA E-Commerce System
 * Logout Page
 */

session_start();
require_once 'config.php';
require_once 'php/auth.php';

// Logout user
Auth::logout_user();

// Redirect to homepage
header('Location: index.php');
exit;
?>
