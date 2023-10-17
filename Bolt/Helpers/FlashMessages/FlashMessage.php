<?php

declare(strict_types=1);

/**
 * =========================================
 * Bolt - FlashMessage =====================
 * =========================================
 */

namespace Bolt\Bolt\Helpers\FlashMessages;



class FlashMessage
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    const INFO = 'info';
    const WARNING = 'warning';

    public static function setMessage($message, $type = self::SUCCESS, $attributes = [])
    {
        // Validate the message type
        $validTypes = [self::SUCCESS, self::ERROR, self::INFO, self::WARNING];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException('Invalid message type');
        }

        // Store the message, type, and attributes in the session
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => $type,
            'attributes' => $attributes,
        ];
    }

    public static function getAndClearMessage()
    {
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']); // Remove the message from the session
        return $message;
    }

    public static function render($message, $type = self::SUCCESS, $classes = '', $attributes = [])
    {
        // You can render a flash message without setting it in the session
        // Useful for cases where you want to display messages directly

        // Validate the message type
        $validTypes = [self::SUCCESS, self::ERROR, self::INFO, self::WARNING];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException('Invalid message type');
        }

        // Generate the HTML for the message
        $html = '<div class="alert ' . $type . ' ' . $classes . '"';

        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        $html .= '>' . htmlspecialchars($message) . '</div>';

        return $html;
    }
}
