<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - DatabaseChannel ==============
 * =====================================
 */

namespace celionatti\Bolt\Notifications\Channels;

use celionatti\Bolt\Database\Database;
use celionatti\Bolt\Notifications\NotificationInterface;

class DatabaseChannel
{
    public function send($notifiable, NotificationInterface $notification)
    {
        $data = $notification->toArray();
        $db = Database::getInstance();

        try {
            $db->insert('notifications', [
                'user_id' => $notifiable->id,
                'type' => get_class($notification),
                'data' => json_encode($data),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->log('Database notification saved for user ID ' . $notifiable->id);
        } catch (\Exception $e) {
            $this->log('Database notification could not be saved. Error: ' . $e->getMessage());
        }
    }

    protected function log($message)
    {
        // Implement logging logic
        file_put_contents('database.log', date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL, FILE_APPEND);
    }
}