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

    private static array $validTypes = [
        self::SUCCESS, 
        self::ERROR, 
        self::INFO, 
        self::WARNING, 
        self::DANGER
    ];

    public static function setMessage(string $message, string $type = self::SUCCESS, array $attributes = []): void
    {
        if (!self::isValidType($type)) {
            throw new \InvalidArgumentException('Invalid message type');
        }

        $_SESSION['__bv_flash_message'] = [
            'message' => $message,
            'type' => $type,
            'attributes' => $attributes,
        ];
    }

    public static function getAndClearMessage(): ?array
    {
        $message = $_SESSION['__bv_flash_message'] ?? null;
        if ($message !== null) {
            unset($_SESSION['__bv_flash_message']);
        }
        return $message;
    }

    public static function render(?string $message = null, string $type = self::SUCCESS, string $classes = '', array $attributes = []): string
    {
        if (!self::isValidType($type)) {
            throw new \InvalidArgumentException('Invalid message type');
        }

        $message = $message ?? self::getAndClearMessage()['message'] ?? '';
        if (empty($message)) {
            return '';
        }

        $htmlAttributes = self::buildAttributes(array_merge(['class' => "alert alert-{$type} {$classes}"], $attributes));
        return "<div{$htmlAttributes}>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</div>";
    }

    private static function isValidType(string $type): bool
    {
        return in_array($type, self::$validTypes, true);
    }

    private static function buildAttributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return $html;
    }
}
