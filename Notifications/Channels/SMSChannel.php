<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - SMSChannel ===================
 * =====================================
 */

namespace celionatti\Bolt\Notifications\Channels;

use celionatti\Bolt\Database\Database;
use celionatti\Bolt\Notifications\NotificationInterface;

class SMSChannel
{
    public function send($notifiable, NotificationInterface $notification)
    {
        $data = $notification->toArray();

        // Implement SMS sending logic here, e.g., using Twilio API
        // Example:
        $twilio = new \Twilio\Rest\Client('ACCOUNT_SID', 'AUTH_TOKEN');
        try {
            $twilio->messages->create($notifiable->phone, [
                'from' => 'YOUR_TWILIO_NUMBER',
                'body' => $data['message'],
            ]);
            $this->log('SMS sent successfully to ' . $notifiable->phone);
        } catch (\Exception $e) {
            $this->log('SMS could not be sent. Error: ' . $e->getMessage());
        }
    }

    protected function log($message)
    {
        // Implement logging logic
        file_put_contents('sms.log', date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL, FILE_APPEND);
    }
}