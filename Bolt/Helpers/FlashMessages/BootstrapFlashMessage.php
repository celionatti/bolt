<?php

declare(strict_types=1);

/**
 * =========================================
 * Bolt - FlashMessage =====================
 * =========================================
 */

namespace Bolt\Bolt\Helpers\FlashMessages;



class BootstrapFlashMessage extends FlashMessage
{
    public static function alertSuccess()
    {
        $message = self::getAndClearMessage();

        if ($message) {
            $messageContent = $message['message'];
            $messageType = $message['type'];
            $messageAttributes = $message['attributes'];

            // Render and display the message with its attributes
            echo FlashMessage::render($messageContent, $messageType, 'alert-success', $messageAttributes);
        }
    }
}
