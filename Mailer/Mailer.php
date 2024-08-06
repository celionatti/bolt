<?php

declare(strict_types=1);

/**
 * ========================================================
 * =====================            =======================
 * Bolt - Mailer Class
 * =====================            =======================
 * ========================================================
 */

namespace celionatti\Bolt\Mailer;

use celionatti\Bolt\View\View;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use celionatti\Bolt\BoltException\BoltException;

class Mailer
{
    protected $mailer;
    protected $view;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->view = new View();
        $this->configureForGmail();
    }

    // Default Gmail Configuration
    public function configureForGmail()
    {
        $this->configure('smtp.gmail.com', 'your-email@gmail.com', 'your-email-password', 587, 'tls');
    }

    public function configure($host, $username, $password, $port = 587, $encryption = 'tls')
    {
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host       = $host;
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $username;
        $this->mailer->Password   = $password;
        $this->mailer->SMTPSecure = $encryption;
        $this->mailer->Port       = $port;

        // Email settings
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->isHTML(true);
    }

    public function send(
        $to, 
        $subject, 
        $template, 
        $data = [], 
        $from = null, 
        $fromName = null, 
        $attachments = [], 
        $cc = [], 
        $bcc = []
    )
    {
        try {
            if ($from && $fromName) {
                $this->mailer->setFrom($from, $fromName);
            } else {
                $this->mailer->setFrom('your-email@gmail.com', 'Your Name');
            }

            // Add recipients
            foreach ((array)$to as $recipient) {
                $this->mailer->addAddress($recipient);
            }

            // Add CC
            foreach ((array)$cc as $ccRecipient) {
                $this->mailer->addCC($ccRecipient);
            }

            // Add BCC
            foreach ((array)$bcc as $bccRecipient) {
                $this->mailer->addBCC($bccRecipient);
            }

            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $this->view->render($template, $data);

            // Add attachments
            foreach ($attachments as $attachment) {
                $this->mailer->addAttachment($attachment);
            }

            $this->mailer->send();
        } catch (Exception $e) {
            // Handle exception
            throw new BoltException("Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}");
        }
    }
}