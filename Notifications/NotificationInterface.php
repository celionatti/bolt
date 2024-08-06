<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - NotificationInterface ========
 * =====================================
 */

namespace celionatti\Bolt\Notifications;

interface NotificationInterface
{
    public function send($notifiable);

    public function via();

    public function toArray();
}