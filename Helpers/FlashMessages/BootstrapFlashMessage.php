<?php

declare(strict_types=1);

/**
 * =========================================
 * Bolt - FlashMessage =====================
 * =========================================
 */

namespace celionatti\Bolt\Helpers\FlashMessages;



class BootstrapFlashMessage extends FlashMessage
{
    public static function alert()
    {
        $message = self::getAndClearMessage();

        if ($message) {
            $messageContent = $message['message'];
            $messageType = $message['type'];
            $messageAttributes = $message['attributes'];

            // Render and display the message with its attributes
            echo FlashMessage::render($messageContent, $messageType, "alert-{$messageType} alert-dismissible fade show mt-5 mx-2 shadow-lg text-uppercase text-center position-fixed position-absolute top-0 start-50 translate-middle", $messageAttributes);
        }
    }
}
