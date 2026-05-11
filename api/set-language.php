<?php
/**
 * LALIBELA GEBEYA E-Commerce System
 * Set Language API Endpoint
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/localization.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language = $_POST['language'] ?? 'en';
    
    if (in_array($language, ['en', 'am'])) {
        set_language($language);
        echo json_encode(['success' => true, 'message' => 'Language changed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid language']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
