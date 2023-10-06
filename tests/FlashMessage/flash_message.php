<?php

namespace App\Core\Support;

class FlashMessage
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    const INFO = 'info';
    const WARNING = 'warning';

    public static function render($message, $type, $classes = '', $attributes = [])
    {
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
