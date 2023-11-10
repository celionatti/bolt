<?php

declare(strict_types=1);

/**
 * =========================================
 * Bolt - FlashMessage =====================
 * =========================================
 */

namespace celionatti\Bolt\Helpers\FlashMessages;



class FlashMessage
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    const INFO = 'info';
    const WARNING = 'warning';
    const DANGER = 'danger';

    public static function setMessage($message, $type = self::SUCCESS, $attributes = [])
    {
        // Validate the message type
        $validTypes = [self::SUCCESS, self::ERROR, self::INFO, self::WARNING, self::DANGER];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException('Invalid message type');
        }

        // Store the message, type, and attributes in the session
        $_SESSION['__flash_message'] = [
            'message' => $message,
            'type' => $type,
            'attributes' => $attributes,
        ];
    }

    public static function getAndClearMessage()
    {
        $message = $_SESSION['__flash_message'] ?? null;
        unset($_SESSION['__flash_message']); // Remove the message from the session
        return $message;
    }

    public static function render($message, $type = self::SUCCESS, $classes = '', $attributes = [])
    {
        // You can render a flash message without setting it in the session
        // Useful for cases where you want to display messages directly

        // Validate the message type
        $validTypes = [self::SUCCESS, self::ERROR, self::INFO, self::WARNING, self::DANGER];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException('Invalid message type');
        }

        // Generate the HTML for the message
        $html = '<div class="position-relative alert ' . $type . ' ' . $classes . '"';

        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        $html .= '>' . htmlspecialchars($message) . '</div>';

        return $html;
    }
}
