<?php

declare(strict_types=1);

/**
 * ========================================================
 * =====================            =======================
 * Mailer Class
 * =====================            =======================
 * ========================================================
 */

namespace Bolt\Bolt\Mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
    }

    public function sendEmail($to, $subject, $message, $fromEmail, $fromName)
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

            // Sender info
            $this->mailer->setFrom($fromEmail, $fromName);

            // Recipient
            $this->mailer->addAddress($to);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $message;

            // Send the email
            $this->mailer->send();

            return true;
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: " . $this->mailer->ErrorInfo;
        }
    }
}
