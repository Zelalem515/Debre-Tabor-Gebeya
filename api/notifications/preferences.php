<?php
/**
 * API Endpoint: Get/Update Notification Preferences
 * 
 * GET /api/notifications/preferences - Get preferences
 * POST /api/notifications/preferences - Update preferences
 */

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../php/auth.php';
require_once __DIR__ . '/../../php/services/NotificationService.php';

// Check authentication
if (!Auth::is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $notificationService = new \App\Services\NotificationService();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get preferences
        $prefs = $notificationService->getPreferences(Auth::get_user_id());
        
        echo json_encode([
            'success' => true,
            'preferences' => $prefs
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update preferences
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = $notificationService->updatePreferences(
            Auth::get_user_id(),
            $data
        );
        
        echo json_encode($result);
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
