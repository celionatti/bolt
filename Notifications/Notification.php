<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - Notification =================
 * =====================================
 */

namespace celionatti\Bolt\Notifications;

use celionatti\Bolt\Notifications\NotificationInterface;

abstract class Notification implements NotificationInterface
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}