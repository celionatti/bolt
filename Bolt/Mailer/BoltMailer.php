<?php

declare(strict_types=1);

/**
 * ========================================================
 * =====================            =======================
 * BoltMailer Class
 * =====================            =======================
 * ========================================================
 */

namespace Bolt\Bolt\Mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class BoltMailer
{
    private $mailer;
    private $logPath;

    public function __construct($logPath = '/path/to/email_log.txt')
    {
        $this->mailer = new PHPMailer(true);
        $this->logPath = $logPath;
    }

    public function sendEmail($recipients, $subject, $htmlMessage, $textMessage, $fromEmail, $fromName, $headers = [], $attachments = [], $inlineImages = [])
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.example.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'your_smtp_username';
            $this->mailer->Password = 'your_smtp_password';
            $this->mailer->SMTPSecure = 'tls';
            $this->mailer->Port = 587;

            // Set custom SMTP options
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // Sender info
            $this->mailer->setFrom($fromEmail, $fromName);

            // Recipients
            foreach ($recipients as $recipient) {
                $this->mailer->addAddress($recipient);
            }

            // Custom headers
            foreach ($headers as $header) {
                $this->mailer->addCustomHeader($header);
            }

            // Attachments
            foreach ($attachments as $attachment) {
                $this->mailer->addAttachment($attachment);
            }

            // Inline images
            foreach ($inlineImages as $cid => $imagePath) {
                $this->mailer->addEmbeddedImage($imagePath, $cid);
                $htmlMessage = str_replace('cid:' . $cid, 'cid:' . $cid, $htmlMessage);
            }


            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlMessage;
            $this->mailer->AltBody = $textMessage;

            // Send the email
            if ($this->mailer->send()) {
                $this->logEmail($recipients, $subject, $htmlMessage);
                return true;
            } else {
                $this->logEmailFailure($recipients, $subject, $htmlMessage, $this->mailer->ErrorInfo);
                return "Message could not be sent. Mailer Error: " . $this->mailer->ErrorInfo;
            }
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: " . $this->mailer->ErrorInfo;
        }
    }

    private function logEmail($recipients, $subject, $message)
    {
        $logEntry = "Sent to: " . implode(', ', $recipients) . PHP_EOL;
        $logEntry .= "Subject: $subject" . PHP_EOL;
        $logEntry .= "Message:" . PHP_EOL . $message . PHP_EOL;
        $logEntry .= str_repeat('-', 40) . PHP_EOL;

        file_put_contents($this->logPath, $logEntry, FILE_APPEND);
    }

    private function logEmailFailure($recipients, $subject, $message, $error)
    {
        $logEntry = "Failed to send to: " . implode(', ', $recipients) . PHP_EOL;
        $logEntry .= "Subject: $subject" . PHP_EOL;
        $logEntry .= "Error: $error" . PHP_EOL;
        $logEntry .= "Message:" . PHP_EOL . $message . PHP_EOL;
        $logEntry .= str_repeat('-', 40) . PHP_EOL;

        file_put_contents($this->logPath, $logEntry, FILE_APPEND);
    }
}
