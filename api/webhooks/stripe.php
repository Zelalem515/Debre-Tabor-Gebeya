<?php
/**
 * Stripe Webhook Endpoint
 * 
 * Receives and processes Stripe webhook events
 * 
 * Endpoint: POST /api/webhooks/stripe
 * 
 * Property Tests:
 * - Property 7: Webhook Idempotency - Same event ID = single processing
 * - Property 12: Webhook Signature Verification - Verify webhook signature
 * 
 * Validates: Requirements 4.4, 4.5
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config/stripe.php';
require_once __DIR__ . '/../../php/services/PaymentService.php';

use App\Services\PaymentService;

// Set response header
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get raw request body
    $payload = file_get_contents('php://input');
    
    // Get Stripe signature from headers
    $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    
    if (empty($signature)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing Stripe signature']);
        exit;
    }
    
    // Initialize payment service
    $paymentService = new PaymentService($pdo);
    
    // Handle webhook
    $result = $paymentService->handleWebhook($payload, $signature);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $result['message'] ?? 'Webhook processed',
            'event_id' => $result['event_id'] ?? null
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $result['message'] ?? 'Webhook processing failed'
        ]);
    }
    
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid webhook signature'
    ]);
    
} catch (\Exception $e) {
    // Other errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Webhook processing error: ' . $e->getMessage()
    ]);
}
