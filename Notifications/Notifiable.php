<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - Notification =================
 * =====================================
 */

namespace celionatti\Bolt\Notifications;

use celionatti\Bolt\Notifications\NotificationInterface;

trait Notifiable
{
    public function notify(NotificationInterface $notification)
    {
        foreach ($notification->via() as $channel) {
            $this->routeNotificationFor($channel)->send($this, $notification);
        }
    }

    protected function routeNotificationFor($channel)
    {
        switch ($channel) {
            case 'email':
                return new Channels\EmailChannel();
            case 'database':
                return new Channels\DatabaseChannel();
            case 'sms':
                return new Channels\SMSChannel();
                // Add more channels as needed
        }
    }
}
