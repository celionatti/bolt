<?php

declare(strict_types=1);

/**
 * ========================================================
 * =====================            =======================
 * Mail Class
 * =====================            =======================
 * ========================================================
 */

namespace celionatti\Bolt\Mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use celionatti\Bolt\Localization\Translation;


class Mailer
{
    private $mail;
    private $translator;

    public function __construct($locale = 'en')
    {
        $this->mail = new PHPMailer(true);
        $this->configure();
        $this->translator = new Translation($locale);
    }

    private function configure()
    {
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.example.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'your-email@example.com';
        $this->mail->Password = 'your-password';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
    }

    public function setFrom($address, $name = '')
    {
        $this->mail->setFrom($address, $name);
    }

    public function addRecipient($address, $name = '')
    {
        $this->mail->addAddress($address, $name);
    }

    public function addAttachment($filePath, $fileName = '')
    {
        $this->mail->addAttachment($filePath, $fileName);
    }

    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    public function addTranslation($locale, $messages)
    {
        $this->translator->addTranslation($locale, $messages);
    }

    public function sendMail($subjectKey, $bodyKey, $isHtml = true)
    {
        try {
            $subject = $this->translator->translate($subjectKey);
            $body = $this->translator->translate($bodyKey);

            $this->mail->isHTML($isHtml);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
            return false;
        }
    }
}
