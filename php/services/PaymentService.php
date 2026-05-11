<?php
/**
 * PaymentService - Stripe Payment Processing
 * 
 * Handles all payment-related operations including:
 * - Payment intent creation
 * - Payment confirmation
 * - Webhook handling
 * - Payment logging and audit trail
 * - Error handling and recovery
 * 
 * Property Tests:
 * - Property 1: Payment Idempotency
 * - Property 7: Webhook Idempotency
 * - Property 11: Audit Trail Completeness
 * - Property 12: Webhook Signature Verification
 */

namespace App\Services;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config/stripe.php';

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use PDO;

class PaymentService {
    
    private $pdo;
    private $logger;
    
    /**
     * Initialize PaymentService
     */
    public function __construct($pdo = null) {
        // Set Stripe API key
        Stripe::setApiKey(STRIPE_SECRET_KEY);
        Stripe::setApiVersion(STRIPE_API_VERSION);
        
        // Store database connection
        $this->pdo = $pdo;
        
        // Initialize logger
        $this->logger = new PaymentLogger($pdo);
    }
    
    /**
     * Create a payment intent for checkout
     * 
     * Property 1: Payment Idempotency
     * - Same idempotency key = single charge
     * 
     * @param int $orderId Order ID
     * @param float $amount Amount in dollars
     * @param string $currency Currency code (default: USD)
     * @param string $idempotencyKey Idempotency key for duplicate prevention
     * @return array Payment intent data
     * @throws ApiErrorException
     */
    public function createPaymentIntent($orderId, $amount, $currency = 'USD', $idempotencyKey = null) {
        try {
            // Generate idempotency key if not provided
            if (empty($idempotencyKey)) {
                $idempotencyKey = $this->generateIdempotencyKey($orderId);
            }
            
            // Create payment intent with Stripe
            $paymentIntent = PaymentIntent::create(
                [
                    'amount' => (int)($amount * 100), // Convert to cents
                    'currency' => strtolower($currency),
                    'metadata' => [
                        'order_id' => $orderId,
                        'timestamp' => time(),
                        'app' => 'debre-tabor-gebeya'
                    ],
                    'description' => "Order #{$orderId} - DEBRETABOR GEBEYA"
                ],
                [
                    'idempotency_key' => $idempotencyKey
                ]
            );
            
            // Log transaction
            $this->logger->logTransaction(
                $orderId,
                $paymentIntent->id,
                $amount,
                'pending',
                'Payment intent created',
                $paymentIntent
            );
            
            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
                'amount' => $amount,
                'currency' => $currency
            ];
            
        } catch (ApiErrorException $e) {
            // Log error
            $this->logger->logError(
                $orderId,
                'create_payment_intent',
                $e->getMessage(),
                $e->getCode()
            );
            
            throw $e;
        }
    }
    
    /**
     * Confirm payment after customer authorization
     * 
     * @param string $paymentIntentId Stripe payment intent ID
     * @return array Confirmation result
     * @throws ApiErrorException
     */
    public function confirmPayment($paymentIntentId) {
        try {
            // Retrieve payment intent from Stripe
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            // Check payment status
            if ($paymentIntent->status === 'succeeded') {
                // Payment successful
                $orderId = $paymentIntent->metadata['order_id'];
                
                // Update order payment status
                $this->updateOrderPaymentStatus(
                    $orderId,
                    'completed',
                    $paymentIntentId,
                    $paymentIntent->amount / 100
                );
                
                // Log successful payment
                $this->logger->logTransaction(
                    $orderId,
                    $paymentIntentId,
                    $paymentIntent->amount / 100,
                    'succeeded',
                    'Payment confirmed',
                    $paymentIntent
                );
                
                return [
                    'success' => true,
                    'status' => 'succeeded',
                    'order_id' => $orderId,
                    'amount' => $paymentIntent->amount / 100,
                    'message' => 'Payment confirmed successfully'
                ];
                
            } elseif ($paymentIntent->status === 'processing') {
                // Payment still processing
                return [
                    'success' => false,
                    'status' => 'processing',
                    'message' => 'Payment is still processing'
                ];
                
            } else {
                // Payment failed or requires action
                $orderId = $paymentIntent->metadata['order_id'] ?? null;
                
                $this->logger->logError(
                    $orderId,
                    'confirm_payment',
                    'Payment status: ' . $paymentIntent->status,
                    'PAYMENT_NOT_CONFIRMED'
                );
                
                return [
                    'success' => false,
                    'status' => $paymentIntent->status,
                    'message' => 'Payment could not be confirmed'
                ];
            }
            
        } catch (ApiErrorException $e) {
            $this->logger->logError(
                null,
                'confirm_payment',
                $e->getMessage(),
                $e->getCode()
            );
            
            throw $e;
        }
    }
    
    /**
     * Handle Stripe webhook events
     * 
     * Property 7: Webhook Idempotency
     * - Same event ID = single processing
     * 
     * Property 12: Webhook Signature Verification
     * - Verify webhook signature using secret key
     * 
     * @param string $payload Raw webhook payload
     * @param string $signature Stripe signature header
     * @return array Processing result
     * @throws SignatureVerificationException
     */
    public function handleWebhook($payload, $signature) {
        try {
            // Verify webhook signature
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                STRIPE_WEBHOOK_SECRET
            );
            
            // Check if event already processed (idempotency)
            if ($this->isWebhookProcessed($event->id)) {
                return [
                    'success' => true,
                    'message' => 'Event already processed',
                    'event_id' => $event->id
                ];
            }
            
            // Process event based on type
            $result = null;
            
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $result = $this->handlePaymentSucceeded($event->data->object);
                    break;
                    
                case 'payment_intent.payment_failed':
                    $result = $this->handlePaymentFailed($event->data->object);
                    break;
                    
                case 'charge.refunded':
                    $result = $this->handleChargeRefunded($event->data->object);
                    break;
                    
                default:
                    $result = [
                        'success' => true,
                        'message' => 'Event type not handled: ' . $event->type
                    ];
            }
            
            // Mark webhook as processed
            $this->markWebhookProcessed($event->id, $event->type);
            
            return $result;
            
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            $this->logger->logError(
                null,
                'webhook_signature_verification',
                'Invalid webhook signature: ' . $e->getMessage(),
                'INVALID_SIGNATURE'
            );
            
            throw $e;
        }
    }
    
    /**
     * Get payment status
     * 
     * @param string $transactionId Stripe payment intent ID
     * @return array Payment status
     */
    public function getPaymentStatus($transactionId) {
        try {
            $paymentIntent = PaymentIntent::retrieve($transactionId);
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => strtoupper($paymentIntent->currency),
                'created' => $paymentIntent->created,
                'metadata' => $paymentIntent->metadata
            ];
            
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Refund a payment
     * 
     * @param string $transactionId Stripe payment intent ID
     * @param float $amount Amount to refund (null = full refund)
     * @return array Refund result
     * @throws ApiErrorException
     */
    public function refundPayment($transactionId, $amount = null) {
        try {
            $paymentIntent = PaymentIntent::retrieve($transactionId);
            
            // Get the charge ID
            if (empty($paymentIntent->charges->data)) {
                throw new \Exception('No charges found for this payment intent');
            }
            
            $chargeId = $paymentIntent->charges->data[0]->id;
            
            // Create refund
            $refund = \Stripe\Refund::create([
                'charge' => $chargeId,
                'amount' => $amount ? (int)($amount * 100) : null
            ]);
            
            // Log refund
            $orderId = $paymentIntent->metadata['order_id'] ?? null;
            $this->logger->logTransaction(
                $orderId,
                $transactionId,
                $refund->amount / 100,
                'refunded',
                'Payment refunded',
                $refund
            );
            
            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'status' => $refund->status
            ];
            
        } catch (ApiErrorException $e) {
            $this->logger->logError(
                null,
                'refund_payment',
                $e->getMessage(),
                $e->getCode()
            );
            
            throw $e;
        }
    }
    
    /**
     * Handle successful payment webhook
     * 
     * @param object $paymentIntent Stripe payment intent object
     * @return array Processing result
     */
    private function handlePaymentSucceeded($paymentIntent) {
        $orderId = $paymentIntent->metadata['order_id'] ?? null;
        
        if (empty($orderId)) {
            return [
                'success' => false,
                'message' => 'Order ID not found in payment metadata'
            ];
        }
        
        // Update order payment status
        $this->updateOrderPaymentStatus(
            $orderId,
            'completed',
            $paymentIntent->id,
            $paymentIntent->amount / 100
        );
        
        // Log event
        $this->logger->logTransaction(
            $orderId,
            $paymentIntent->id,
            $paymentIntent->amount / 100,
            'succeeded',
            'Webhook: payment_intent.succeeded',
            $paymentIntent
        );
        
        return [
            'success' => true,
            'message' => 'Payment succeeded',
            'order_id' => $orderId
        ];
    }
    
    /**
     * Handle failed payment webhook
     * 
     * @param object $paymentIntent Stripe payment intent object
     * @return array Processing result
     */
    private function handlePaymentFailed($paymentIntent) {
        $orderId = $paymentIntent->metadata['order_id'] ?? null;
        
        if (empty($orderId)) {
            return [
                'success' => false,
                'message' => 'Order ID not found in payment metadata'
            ];
        }
        
        // Update order payment status
        $this->updateOrderPaymentStatus(
            $orderId,
            'failed',
            $paymentIntent->id,
            $paymentIntent->amount / 100,
            $paymentIntent->last_payment_error->message ?? 'Unknown error'
        );
        
        // Log event
        $this->logger->logError(
            $orderId,
            'payment_failed',
            $paymentIntent->last_payment_error->message ?? 'Unknown error',
            'PAYMENT_FAILED'
        );
        
        return [
            'success' => true,
            'message' => 'Payment failed',
            'order_id' => $orderId,
            'error' => $paymentIntent->last_payment_error->message ?? 'Unknown error'
        ];
    }
    
    /**
     * Handle charge refunded webhook
     * 
     * @param object $charge Stripe charge object
     * @return array Processing result
     */
    private function handleChargeRefunded($charge) {
        // Log refund event
        $this->logger->logTransaction(
            null,
            $charge->payment_intent,
            $charge->amount_refunded / 100,
            'refunded',
            'Webhook: charge.refunded',
            $charge
        );
        
        return [
            'success' => true,
            'message' => 'Charge refunded',
            'charge_id' => $charge->id
        ];
    }
    
    /**
     * Update order payment status in database
     * 
     * @param int $orderId Order ID
     * @param string $status Payment status
     * @param string $transactionId Stripe transaction ID
     * @param float $amount Payment amount
     * @param string $errorMessage Error message (if any)
     */
    private function updateOrderPaymentStatus($orderId, $status, $transactionId, $amount, $errorMessage = null) {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE orders 
                 SET payment_status = ?, 
                     transaction_id = ?, 
                     stripe_payment_intent_id = ?,
                     updated_at = NOW()
                 WHERE id = ?"
            );
            
            $stmt->execute([$status, $transactionId, $transactionId, $orderId]);
            
            // Also update payments table
            $stmt = $this->pdo->prepare(
                "INSERT INTO payments (order_id, stripe_payment_intent_id, amount, status, error_message)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 status = ?, error_message = ?, updated_at = NOW()"
            );
            
            $stmt->execute([
                $orderId,
                $transactionId,
                $amount,
                $status,
                $errorMessage,
                $status,
                $errorMessage
            ]);
            
        } catch (\PDOException $e) {
            $this->logger->logError(
                $orderId,
                'update_order_payment_status',
                $e->getMessage(),
                'DATABASE_ERROR'
            );
        }
    }
    
    /**
     * Generate idempotency key
     * 
     * @param int $orderId Order ID
     * @return string Idempotency key
     */
    private function generateIdempotencyKey($orderId) {
        return hash('sha256', $orderId . time() . uniqid());
    }
    
    /**
     * Check if webhook already processed
     * 
     * @param string $eventId Stripe event ID
     * @return bool True if already processed
     */
    private function isWebhookProcessed($eventId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM payment_webhooks WHERE stripe_event_id = ? AND processed = TRUE"
            );
            $stmt->execute([$eventId]);
            
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    /**
     * Mark webhook as processed
     * 
     * @param string $eventId Stripe event ID
     * @param string $eventType Event type
     */
    private function markWebhookProcessed($eventId, $eventType) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO payment_webhooks (stripe_event_id, event_type, processed, processed_at)
                 VALUES (?, ?, TRUE, NOW())
                 ON DUPLICATE KEY UPDATE processed = TRUE, processed_at = NOW()"
            );
            
            $stmt->execute([$eventId, $eventType]);
        } catch (\PDOException $e) {
            // Log but don't fail
        }
    }
}

/**
 * PaymentLogger - Audit trail for payment transactions
 */
class PaymentLogger {
    
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Log payment transaction
     * 
     * Property 11: Audit Trail Completeness
     * - All transactions logged with details
     */
    public function logTransaction($orderId, $transactionId, $amount, $status, $message, $data) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO payments (order_id, stripe_payment_intent_id, amount, status, payment_method, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            
            $paymentMethod = $data->payment_method ?? 'unknown';
            
            $stmt->execute([
                $orderId,
                $transactionId,
                $amount,
                $status,
                $paymentMethod
            ]);
            
        } catch (\PDOException $e) {
            error_log("Payment logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Log payment error
     */
    public function logError($orderId, $operation, $message, $code) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO payments (order_id, status, error_message, created_at)
                 VALUES (?, 'failed', ?, NOW())"
            );
            
            $stmt->execute([
                $orderId,
                "[{$operation}] {$message} (Code: {$code})"
            ]);
            
        } catch (\PDOException $e) {
            error_log("Error logging error: " . $e->getMessage());
        }
    }
}
