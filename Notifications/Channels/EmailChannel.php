<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - EmailChannel =================
 * =====================================
 */

namespace celionatti\Bolt\Notifications\Channels;

use celionatti\Bolt\Notifications\NotificationInterface;

class EmailChannel
{
    public function send($notifiable, NotificationInterface $notification)
    {
        $data = $notification->toArray();

        // Example using PHPMailer
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@example.com';
        $mail->Password = 'your-email-password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('from@example.com', 'Mailer');
        $mail->addAddress($notifiable->email, $notifiable->name);

        $mail->isHTML(true);
        $mail->Subject = $data['subject'];
        $mail->Body    = $data['message'];

        try {
            $mail->send();
            $this->log('Email sent successfully to ' . $notifiable->email);
        } catch (\Exception $e) {
            $this->log('Email could not be sent. Mailer Error: ' . $mail->ErrorInfo);
        }
    }

    protected function log($message)
    {
        // Implement logging logic
        file_put_contents('email.log', date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL, FILE_APPEND);
    }
}