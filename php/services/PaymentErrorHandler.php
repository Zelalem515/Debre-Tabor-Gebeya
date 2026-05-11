<?php
/**
 * PaymentErrorHandler - Payment Error Handling & Recovery
 * 
 * Handles payment errors with:
 * - User-friendly error messages
 * - Automatic retry logic for transient errors
 * - Error logging and alerting
 * - Recovery strategies
 * 
 * Validates: Requirements 1.4
 */

namespace App\Services;

require_once __DIR__ . '/../../config.php';

class PaymentErrorHandler {
    
    // Error types
    const ERROR_CARD_DECLINED = 'card_declined';
    const ERROR_INSUFFICIENT_FUNDS = 'insufficient_funds';
    const ERROR_LOST_CARD = 'lost_card';
    const ERROR_STOLEN_CARD = 'stolen_card';
    const ERROR_EXPIRED_CARD = 'expired_card';
    const ERROR_INCORRECT_CVC = 'incorrect_cvc';
    const ERROR_PROCESSING_ERROR = 'processing_error';
    const ERROR_RATE_LIMIT = 'rate_limit';
    const ERROR_AUTHENTICATION_REQUIRED = 'authentication_required';
    const ERROR_NETWORK_ERROR = 'network_error';
    const ERROR_INVALID_CARD = 'invalid_card';
    const ERROR_UNKNOWN = 'unknown_error';
    
    // Retry configuration
    const MAX_RETRIES = 3;
    const INITIAL_RETRY_DELAY = 1; // seconds
    const MAX_RETRY_DELAY = 60; // seconds
    
    private $pdo;
    private $logger;
    
    /**
     * Initialize error handler
     */
    public function __construct($pdo = null) {
        $this->pdo = $pdo;
        $this->logger = new PaymentErrorLogger($pdo);
    }
    
    /**
     * Handle payment error
     * 
     * @param \Exception $exception Stripe exception
     * @param int $orderId Order ID
     * @param int $retryCount Current retry count
     * @return array Error response with user message and recovery action
     */
    public function handleError($exception, $orderId = null, $retryCount = 0) {
        try {
            // Determine error type
            $errorType = $this->determineErrorType($exception);
            
            // Get user-friendly message
            $userMessage = $this->getUserMessage($errorType);
            
            // Determine if retryable
            $isRetryable = $this->isRetryable($errorType, $retryCount);
            
            // Log error
            $this->logger->logPaymentError(
                $orderId,
                $errorType,
                $exception->getMessage(),
                $retryCount,
                $isRetryable
            );
            
            // Determine recovery action
            $recoveryAction = $this->getRecoveryAction($errorType);
            
            return [
                'success' => false,
                'error_type' => $errorType,
                'user_message' => $userMessage,
                'technical_message' => $exception->getMessage(),
                'is_retryable' => $isRetryable,
                'retry_count' => $retryCount,
                'recovery_action' => $recoveryAction,
                'should_retry' => $isRetryable && $retryCount < self::MAX_RETRIES,
                'retry_delay' => $this->calculateRetryDelay($retryCount)
            ];
            
        } catch (\Exception $e) {
            // Fallback error handling
            return [
                'success' => false,
                'error_type' => self::ERROR_UNKNOWN,
                'user_message' => 'An unexpected error occurred. Please try again later.',
                'technical_message' => $e->getMessage(),
                'is_retryable' => false,
                'recovery_action' => 'contact_support'
            ];
        }
    }
    
    /**
     * Determine error type from exception
     * 
     * @param \Exception $exception Stripe exception
     * @return string Error type constant
     */
    private function determineErrorType($exception) {
        $message = strtolower($exception->getMessage());
        $code = $exception->getCode() ?? '';
        
        // Check for specific error patterns
        if (strpos($message, 'card_declined') !== false || $code === 'card_declined') {
            return self::ERROR_CARD_DECLINED;
        }
        
        if (strpos($message, 'insufficient_funds') !== false) {
            return self::ERROR_INSUFFICIENT_FUNDS;
        }
        
        if (strpos($message, 'lost_card') !== false) {
            return self::ERROR_LOST_CARD;
        }
        
        if (strpos($message, 'stolen_card') !== false) {
            return self::ERROR_STOLEN_CARD;
        }
        
        if (strpos($message, 'expired') !== false || strpos($message, 'exp') !== false) {
            return self::ERROR_EXPIRED_CARD;
        }
        
        if (strpos($message, 'cvc') !== false || strpos($message, 'cvv') !== false) {
            return self::ERROR_INCORRECT_CVC;
        }
        
        if (strpos($message, 'processing_error') !== false) {
            return self::ERROR_PROCESSING_ERROR;
        }
        
        if (strpos($message, 'rate_limit') !== false || $code === 429) {
            return self::ERROR_RATE_LIMIT;
        }
        
        if (strpos($message, 'authentication') !== false) {
            return self::ERROR_AUTHENTICATION_REQUIRED;
        }
        
        if (strpos($message, 'network') !== false || strpos($message, 'connection') !== false) {
            return self::ERROR_NETWORK_ERROR;
        }
        
        if (strpos($message, 'invalid') !== false && strpos($message, 'card') !== false) {
            return self::ERROR_INVALID_CARD;
        }
        
        return self::ERROR_UNKNOWN;
    }
    
    /**
     * Get user-friendly error message
     * 
     * @param string $errorType Error type
     * @return string User message
     */
    private function getUserMessage($errorType) {
        $messages = [
            self::ERROR_CARD_DECLINED => 'Your card was declined. Please check your card details and try again.',
            self::ERROR_INSUFFICIENT_FUNDS => 'Your card has insufficient funds. Please use a different card or add funds.',
            self::ERROR_LOST_CARD => 'Your card has been reported as lost. Please use a different card.',
            self::ERROR_STOLEN_CARD => 'Your card has been reported as stolen. Please use a different card.',
            self::ERROR_EXPIRED_CARD => 'Your card has expired. Please use a different card or update your card details.',
            self::ERROR_INCORRECT_CVC => 'The security code (CVC) you entered is incorrect. Please check and try again.',
            self::ERROR_PROCESSING_ERROR => 'A processing error occurred. Please try again in a few moments.',
            self::ERROR_RATE_LIMIT => 'Too many requests. Please wait a moment and try again.',
            self::ERROR_AUTHENTICATION_REQUIRED => 'Your card requires additional authentication. Please complete the verification process.',
            self::ERROR_NETWORK_ERROR => 'A network error occurred. Please check your connection and try again.',
            self::ERROR_INVALID_CARD => 'Your card details are invalid. Please check and try again.',
            self::ERROR_UNKNOWN => 'An error occurred while processing your payment. Please try again or contact support.'
        ];
        
        return $messages[$errorType] ?? $messages[self::ERROR_UNKNOWN];
    }
    
    /**
     * Determine if error is retryable
     * 
     * @param string $errorType Error type
     * @param int $retryCount Current retry count
     * @return bool True if error should be retried
     */
    private function isRetryable($errorType, $retryCount) {
        // Don't retry if max retries exceeded
        if ($retryCount >= self::MAX_RETRIES) {
            return false;
        }
        
        // Retryable errors
        $retryableErrors = [
            self::ERROR_PROCESSING_ERROR,
            self::ERROR_RATE_LIMIT,
            self::ERROR_NETWORK_ERROR
        ];
        
        return in_array($errorType, $retryableErrors);
    }
    
    /**
     * Get recovery action for error
     * 
     * @param string $errorType Error type
     * @return string Recovery action
     */
    private function getRecoveryAction($errorType) {
        $actions = [
            self::ERROR_CARD_DECLINED => 'try_different_card',
            self::ERROR_INSUFFICIENT_FUNDS => 'try_different_card',
            self::ERROR_LOST_CARD => 'try_different_card',
            self::ERROR_STOLEN_CARD => 'try_different_card',
            self::ERROR_EXPIRED_CARD => 'update_card',
            self::ERROR_INCORRECT_CVC => 'retry_payment',
            self::ERROR_PROCESSING_ERROR => 'retry_payment',
            self::ERROR_RATE_LIMIT => 'retry_payment',
            self::ERROR_AUTHENTICATION_REQUIRED => 'complete_authentication',
            self::ERROR_NETWORK_ERROR => 'retry_payment',
            self::ERROR_INVALID_CARD => 'retry_payment',
            self::ERROR_UNKNOWN => 'contact_support'
        ];
        
        return $actions[$errorType] ?? 'contact_support';
    }
    
    /**
     * Calculate retry delay with exponential backoff
     * 
     * @param int $retryCount Current retry count
     * @return int Delay in seconds
     */
    private function calculateRetryDelay($retryCount) {
        // Exponential backoff: 1s, 2s, 4s, 8s, etc.
        $delay = self::INITIAL_RETRY_DELAY * pow(2, $retryCount);
        
        // Cap at max delay
        return min($delay, self::MAX_RETRY_DELAY);
    }
    
    /**
     * Validate card format
     * 
     * @param string $cardNumber Card number
     * @param string $expMonth Expiration month
     * @param string $expYear Expiration year
     * @param string $cvc CVC code
     * @return array Validation result
     */
    public function validateCardFormat($cardNumber, $expMonth, $expYear, $cvc) {
        $errors = [];
        
        // Validate card number (Luhn algorithm)
        if (!$this->validateLuhn($cardNumber)) {
            $errors[] = 'Invalid card number';
        }
        
        // Validate expiration
        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');
        $expMonth = (int)$expMonth;
        $expYear = (int)$expYear;
        
        if ($expYear < $currentYear || ($expYear === $currentYear && $expMonth < $currentMonth)) {
            $errors[] = 'Card has expired';
        }
        
        // Validate CVC
        if (!preg_match('/^\d{3,4}$/', $cvc)) {
            $errors[] = 'Invalid security code';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate card number using Luhn algorithm
     * 
     * @param string $cardNumber Card number
     * @return bool True if valid
     */
    private function validateLuhn($cardNumber) {
        // Remove spaces and dashes
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        // Check length
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }
        
        // Luhn algorithm
        $sum = 0;
        $isEven = false;
        
        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $digit = (int)$cardNumber[$i];
            
            if ($isEven) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
            $isEven = !$isEven;
        }
        
        return $sum % 10 === 0;
    }
    
    /**
     * Create alert for payment error
     * 
     * @param int $orderId Order ID
     * @param string $errorType Error type
     * @param string $message Error message
     */
    public function createAlert($orderId, $errorType, $message) {
        try {
            if (!$this->pdo) {
                return;
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO alerts (type, order_id, message, severity, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            // Determine severity
            $severity = 'warning';
            if (in_array($errorType, [
                self::ERROR_LOST_CARD,
                self::ERROR_STOLEN_CARD,
                self::ERROR_AUTHENTICATION_REQUIRED
            ])) {
                $severity = 'critical';
            }
            
            $stmt->execute([
                'payment_error',
                $orderId,
                $message,
                $severity
            ]);
            
        } catch (\Exception $e) {
            error_log("Alert creation error: " . $e->getMessage());
        }
    }
}

/**
 * PaymentErrorLogger - Log payment errors
 */
class PaymentErrorLogger {
    
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Log payment error
     */
    public function logPaymentError($orderId, $errorType, $message, $retryCount, $isRetryable) {
        try {
            if (!$this->pdo) {
                error_log("Payment Error [{$errorType}]: {$message}");
                return;
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO payment_errors (order_id, error_type, message, retry_count, is_retryable, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $orderId,
                $errorType,
                $message,
                $retryCount,
                $isRetryable ? 1 : 0
            ]);
            
        } catch (\Exception $e) {
            error_log("Payment error logging failed: " . $e->getMessage());
        }
    }
}
