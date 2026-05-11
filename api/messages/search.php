<?php
/**
 * API Endpoint: Search Messages
 * 
 * GET /api/messages/search
 * 
 * Parameters:
 * - query (string) - Search query
 * - limit (int, optional) - Results limit (default: 20)
 */

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../php/auth.php';
require_once __DIR__ . '/../../php/services/MessageService.php';

// Check authentication
if (!Auth::is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Validate required parameters
if (empty($_GET['query'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing query parameter']);
    exit;
}

try {
    $query = $_GET['query'];
    $limit = (int)($_GET['limit'] ?? 20);
    
    // Validate query length
    if (strlen($query) < 2) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Query must be at least 2 characters']);
        exit;
    }
    
    if (strlen($query) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Query is too long']);
        exit;
    }
    
    // Validate limit
    if ($limit < 1 || $limit > 100) $limit = 20;
    
    $messageService = new \App\Services\MessageService();
    
    $results = $messageService->searchMessages(
        Auth::get_user_id(),
        $query,
        $limit
    );
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'count' => count($results)
    ]);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
