<?php

declare(strict_types=1);

/**
 * ========================================================
 * =====================            =======================
 * Mailer Class
 * =====================            =======================
 * ========================================================
 */

namespace Bolt\Bolt\Mailer;

use Bolt\Bolt\Config;
use Bolt\Bolt\Helpers\FlashMessages\BootstrapFlashMessage;
use Bolt\Bolt\Helpers\FlashMessages\FlashMessage;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
    }

    public function sendEmail($to, $subject, $message, $fromEmail, $fromName)
    {
        try {
            // Server settings
            $this->mailer->SMTPDebug = 2;
            $this->mailer->isSMTP();
            $this->mailer->Host = Config::get("mailer_host") ?? 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = Config::get("mailer_email") ?? "mailer_email";
            $this->mailer->Password = Config::get("mailer_password") ?? "mailer_password";
            $this->mailer->SMTPSecure = 'tls';
            $this->mailer->Port = 587;

            // Sender info
            $this->mailer->setFrom($fromEmail, $fromName);

            // Recipient
            $this->mailer->addAddress($to);

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $message;

            if($this->mailer->send()) {
                BootstrapFlashMessage::setMessage("MAIL SENT");
                dd("Sent mail");
                return true;
            }
            // Send the email
            // $this->mailer->send();


        } catch (Exception $e) {
            dd("error mail");
            return "Message could not be sent. Mailer Error: " . $this->mailer->ErrorInfo;
        }
    }
}
