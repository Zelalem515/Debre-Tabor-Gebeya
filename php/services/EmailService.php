<?php
/**
 * EmailService - Email Notification System
 * 
 * Handles all email-related operations including:
 * - Email queue management
 * - Template rendering and substitution
 * - Email sending via multiple providers (SendGrid, Mailgun, SMTP)
 * - Retry logic with exponential backoff
 * - Email logging and audit trail
 * - Delivery status tracking
 * 
 * Property Tests:
 * - Property 6: Email Template Substitution
 * - Property 10: Email Retry Logic
 * - Property 11: Audit Trail Completeness
 */

namespace App\Services;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config/email.php';

use SendGrid\Mail\Mail;
use SendGrid\Mail\To;
use SendGrid\Mail\Content;
use SendGrid\Mail\Subject;
use SendGrid\Mail\From;
use Mailgun\Mailgun;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PDO;

class EmailService {
    
    private $pdo;
    private $provider;
    private $logger;
    
    // Email status constants
    const STATUS_QUEUED = 'queued';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_BOUNCED = 'bounced';
    
    /**
     * Initialize EmailService
     */
    public function __construct($pdo = null) {
        $this->pdo = $pdo;
        $this->provider = EMAIL_PROVIDER;
        $this->logger = new EmailLogger($pdo);
    }
    
    /**
     * Queue an email for sending
     * 
     * @param string $recipientEmail Recipient email address
     * @param string $templateName Template name (e.g., 'order_confirmation')
     * @param array $templateData Data to substitute in template
     * @param array $options Additional options (cc, bcc, priority, etc.)
     * @return array Result with email_id and status
     */
    public function queueEmail($recipientEmail, $templateName, $templateData = [], $options = []) {
        try {
            // Validate email
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("Invalid email address: {$recipientEmail}");
            }
            
            // Get template
            $template = $this->getTemplate($templateName);
            if (!$template) {
                throw new \Exception("Template not found: {$templateName}");
            }
            
            // Render template
            $subject = $this->renderTemplate($template['subject'], $templateData);
            $htmlContent = $this->renderTemplate($template['html'], $templateData);
            $textContent = $this->renderTemplate($template['text'] ?? '', $templateData);
            
            // Prepare email data
            $emailData = [
                'recipient_email' => $recipientEmail,
                'template_name' => $templateName,
                'subject' => $subject,
                'html_content' => $htmlContent,
                'text_content' => $textContent,
                'cc_emails' => $options['cc'] ?? null,
                'bcc_emails' => $options['bcc'] ?? null,
                'priority' => $options['priority'] ?? 'normal',
                'status' => self::STATUS_QUEUED,
                'retry_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'scheduled_at' => $options['scheduled_at'] ?? date('Y-m-d H:i:s'),
                'metadata' => json_encode($templateData)
            ];
            
            // Store in database
            $emailId = $this->storeEmailInQueue($emailData);
            
            // Log email queued
            $this->logger->logEmailEvent(
                $emailId,
                $recipientEmail,
                self::STATUS_QUEUED,
                'Email queued for sending',
                $emailData
            );
            
            return [
                'success' => true,
                'email_id' => $emailId,
                'status' => self::STATUS_QUEUED,
                'message' => 'Email queued successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logger->logError('queueEmail', $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Render template with data substitution
     * 
     * Property 6: Email Template Substitution
     * - All placeholders should be replaced with actual values
     * 
     * @param string $template Template content
     * @param array $data Data to substitute
     * @return string Rendered template
     */
    public function renderTemplate($template, $data = []) {
        if (empty($template)) {
            return '';
        }
        
        $rendered = $template;
        
        // Replace placeholders
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $rendered = str_replace($placeholder, htmlspecialchars($value), $rendered);
        }
        
        // Remove any unreplaced placeholders
        $rendered = preg_replace('/\{[^}]+\}/', '', $rendered);
        
        return $rendered;
    }
    
    /**
     * Send queued emails (background worker)
     * 
     * @param int $batchSize Number of emails to send in one batch
     * @return array Result with sent count and failed count
     */
    public function processEmailQueue($batchSize = EMAIL_QUEUE_BATCH_SIZE) {
        try {
            // Get queued emails
            $emails = $this->getQueuedEmails($batchSize);
            
            $sentCount = 0;
            $failedCount = 0;
            
            foreach ($emails as $email) {
                $result = $this->sendEmail($email);
                
                if ($result['success']) {
                    $sentCount++;
                    $this->updateEmailStatus($email['id'], self::STATUS_SENT);
                    $this->logger->logEmailEvent(
                        $email['id'],
                        $email['recipient_email'],
                        self::STATUS_SENT,
                        'Email sent successfully'
                    );
                } else {
                    $failedCount++;
                    $this->updateEmailStatus($email['id'], self::STATUS_FAILED);
                    $this->logger->logEmailEvent(
                        $email['id'],
                        $email['recipient_email'],
                        self::STATUS_FAILED,
                        'Email sending failed: ' . $result['error']
                    );
                }
            }
            
            return [
                'success' => true,
                'sent' => $sentCount,
                'failed' => $failedCount,
                'total' => count($emails)
            ];
            
        } catch (\Exception $e) {
            $this->logger->logError('processEmailQueue', $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send a single email
     * 
     * @param array $email Email data
     * @return array Result with success status
     */
    private function sendEmail($email) {
        try {
            switch ($this->provider) {
                case 'sendgrid':
                    return $this->sendViaSendGrid($email);
                case 'mailgun':
                    return $this->sendViaMailgun($email);
                case 'smtp':
                    return $this->sendViaSMTP($email);
                default:
                    throw new \Exception("Unknown email provider: {$this->provider}");
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send email via SendGrid
     */
    private function sendViaSendGrid($email) {
        try {
            $mail = new Mail();
            $mail->setFrom(SENDGRID_FROM_EMAIL, SENDGRID_FROM_NAME);
            $mail->setSubject($email['subject']);
            $mail->addTo($email['recipient_email']);
            $mail->addContent('text/html', $email['html_content']);
            
            if (!empty($email['text_content'])) {
                $mail->addContent('text/plain', $email['text_content']);
            }
            
            // Add CC/BCC if provided
            if (!empty($email['cc_emails'])) {
                $ccEmails = explode(',', $email['cc_emails']);
                foreach ($ccEmails as $cc) {
                    $mail->addCc(trim($cc));
                }
            }
            
            if (!empty($email['bcc_emails'])) {
                $bccEmails = explode(',', $email['bcc_emails']);
                foreach ($bccEmails as $bcc) {
                    $mail->addBcc(trim($bcc));
                }
            }
            
            // Set metadata
            $mail->setReplyToList([
                'email' => SENDGRID_FROM_EMAIL,
                'name' => SENDGRID_FROM_NAME
            ]);
            
            $sendgrid = new \SendGrid(SENDGRID_API_KEY);
            $response = $sendgrid->send($mail);
            
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return ['success' => true];
            } else {
                return [
                    'success' => false,
                    'error' => 'SendGrid error: ' . $response->statusCode()
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send email via Mailgun
     */
    private function sendViaMailgun($email) {
        try {
            $mg = Mailgun::create(MAILGUN_API_KEY);
            
            $result = $mg->messages()->send(MAILGUN_DOMAIN, [
                'from' => MAILGUN_FROM_NAME . ' <' . MAILGUN_FROM_EMAIL . '>',
                'to' => $email['recipient_email'],
                'subject' => $email['subject'],
                'html' => $email['html_content'],
                'text' => $email['text_content'] ?? '',
                'cc' => $email['cc_emails'] ?? '',
                'bcc' => $email['bcc_emails'] ?? ''
            ]);
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send email via SMTP
     */
    private function sendViaSMTP($email) {
        try {
            $mail = new PHPMailer(true);
            
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port = SMTP_PORT;
            $mail->Timeout = EMAIL_TIMEOUT;
            
            // Email content
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email['recipient_email']);
            $mail->Subject = $email['subject'];
            $mail->isHTML(true);
            $mail->Body = $email['html_content'];
            $mail->AltBody = $email['text_content'] ?? '';
            
            // Add CC/BCC if provided
            if (!empty($email['cc_emails'])) {
                $ccEmails = explode(',', $email['cc_emails']);
                foreach ($ccEmails as $cc) {
                    $mail->addCC(trim($cc));
                }
            }
            
            if (!empty($email['bcc_emails'])) {
                $bccEmails = explode(',', $email['bcc_emails']);
                foreach ($bccEmails as $bcc) {
                    $mail->addBCC(trim($bcc));
                }
            }
            
            $mail->send();
            return ['success' => true];
            
        } catch (PHPMailerException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Retry failed emails with exponential backoff
     * 
     * Property 10: Email Retry Logic
     * - Failed emails should retry up to 3 times
     * - Exponential backoff between retries
     * 
     * @param int $maxRetries Maximum retry attempts
     * @return array Result with retry count
     */
    public function retryFailedEmails($maxRetries = EMAIL_RETRY_ATTEMPTS) {
        try {
            // Get failed emails that haven't exceeded retry limit
            $failedEmails = $this->getFailedEmails($maxRetries);
            
            $retryCount = 0;
            
            foreach ($failedEmails as $email) {
                // Calculate backoff delay
                $backoffDelay = $this->calculateBackoffDelay($email['retry_count']);
                
                // Check if enough time has passed
                $lastAttempt = strtotime($email['last_attempt_at']);
                $now = time();
                
                if (($now - $lastAttempt) >= $backoffDelay) {
                    // Attempt to send
                    $result = $this->sendEmail($email);
                    
                    if ($result['success']) {
                        $this->updateEmailStatus($email['id'], self::STATUS_SENT);
                        $this->logger->logEmailEvent(
                            $email['id'],
                            $email['recipient_email'],
                            self::STATUS_SENT,
                            'Email sent on retry attempt ' . ($email['retry_count'] + 1)
                        );
                    } else {
                        // Increment retry count
                        $this->incrementRetryCount($email['id']);
                        $this->logger->logEmailEvent(
                            $email['id'],
                            $email['recipient_email'],
                            self::STATUS_FAILED,
                            'Retry attempt ' . ($email['retry_count'] + 1) . ' failed: ' . $result['error']
                        );
                    }
                    
                    $retryCount++;
                }
            }
            
            return [
                'success' => true,
                'retried' => $retryCount,
                'total_failed' => count($failedEmails)
            ];
            
        } catch (\Exception $e) {
            $this->logger->logError('retryFailedEmails', $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate exponential backoff delay
     * 
     * @param int $retryCount Current retry count
     * @return int Delay in seconds
     */
    private function calculateBackoffDelay($retryCount) {
        // Exponential backoff: 5 min, 15 min, 30 min
        $delays = [300, 900, 1800];
        return $delays[min($retryCount, count($delays) - 1)];
    }
    
    /**
     * Get template data
     */
    private function getTemplate($templateName) {
        $templates = EMAIL_TEMPLATES;
        
        if (!isset($templates[$templateName])) {
            return null;
        }
        
        $template = $templates[$templateName];
        $filePath = EMAIL_TEMPLATES_PATH . '/' . $template['file'];
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $htmlContent = file_get_contents($filePath);
        
        return [
            'name' => $template['name'],
            'subject' => $template['subject'],
            'html' => $htmlContent,
            'text' => strip_tags($htmlContent)
        ];
    }
    
    /**
     * Store email in queue
     */
    private function storeEmailInQueue($emailData) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO emails (
                    recipient_email, template_name, subject, html_content, text_content,
                    cc_emails, bcc_emails, priority, status, retry_count,
                    created_at, scheduled_at, metadata
                ) VALUES (
                    :recipient_email, :template_name, :subject, :html_content, :text_content,
                    :cc_emails, :bcc_emails, :priority, :status, :retry_count,
                    :created_at, :scheduled_at, :metadata
                )
            ");
            
            $stmt->execute($emailData);
            return $this->pdo->lastInsertId();
            
        } catch (\PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Get queued emails
     */
    private function getQueuedEmails($limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM emails
                WHERE status = :status
                AND scheduled_at <= NOW()
                ORDER BY priority DESC, created_at ASC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':status', self::STATUS_QUEUED);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Get failed emails for retry
     */
    private function getFailedEmails($maxRetries) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM emails
                WHERE status = :status
                AND retry_count < :max_retries
                ORDER BY last_attempt_at ASC
                LIMIT 100
            ");
            
            $stmt->bindValue(':status', self::STATUS_FAILED);
            $stmt->bindValue(':max_retries', $maxRetries, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Update email status
     */
    private function updateEmailStatus($emailId, $status) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE emails
                SET status = :status, updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':status' => $status,
                ':id' => $emailId
            ]);
            
        } catch (\PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Increment retry count
     */
    private function incrementRetryCount($emailId) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE emails
                SET retry_count = retry_count + 1,
                    last_attempt_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([':id' => $emailId]);
            
        } catch (\PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }
}

/**
 * EmailLogger - Log email events and errors
 */
class EmailLogger {
    
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Log email event
     */
    public function logEmailEvent($emailId, $recipientEmail, $status, $message, $data = []) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO email_logs (
                    email_id, recipient_email, status, message, data, created_at
                ) VALUES (
                    :email_id, :recipient_email, :status, :message, :data, NOW()
                )
            ");
            
            $stmt->execute([
                ':email_id' => $emailId,
                ':recipient_email' => $recipientEmail,
                ':status' => $status,
                ':message' => $message,
                ':data' => json_encode($data)
            ]);
            
        } catch (\PDOException $e) {
            error_log("Email logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Log error
     */
    public function logError($method, $error) {
        error_log("EmailService::{$method} - {$error}");
    }
}
