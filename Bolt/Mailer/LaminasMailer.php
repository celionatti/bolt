<?php

declare(strict_types=1);

/**
 * ========================================================
 * =====================            =======================
 * Laminas Mailer Class
 * =====================            =======================
 * ========================================================
 */

namespace Bolt\Bolt\Mailer;

use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Message;

class LaminasMailer
{
    private $smtpConfig;

    public function __construct()
    {
        // Configure your SMTP settings here
        $this->smtpConfig = [
            'name' => 'your_smtp_server',
            'host' => 'your_smtp_host',
            'port' => 587, // Replace with your SMTP server port
            'connection_class' => 'login',
            'connection_config' => [
                'username' => 'your_email@example.com',
                'password' => 'your_password',
                'ssl' => 'tls', // Use 'ssl' for SSL encryption or 'tls' for TLS encryption
            ],
        ];
    }

    public function sendEmail($from, $to, $subject, $body)
    {
        $transport = new SmtpTransport(new SmtpOptions($this->smtpConfig));

        $message = new Message();
        $message->addFrom($from)
            ->addTo($to)
            ->setSubject($subject)
            ->setBody($body);

        try {
            $transport->send($message);
            return true; // Email sent successfully
        } catch (\Exception $e) {
            // Handle any exceptions here, e.g., log errors
            return false; // Email sending failed
        }
    }
}
