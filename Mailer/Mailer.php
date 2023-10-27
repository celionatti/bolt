<?php

declare(strict_types=1);

/**
 * ========================================================
 * =====================            =======================
 * Mailer Class
 * =====================            =======================
 * ========================================================
 */

namespace celionatti\Bolt\Mailer;


class Mailer
{
    private $to;
    private $subject;
    private $message;
    private $headers;

    public function __construct()
    {
        ini_set("SMTP", "smtp.gmail.com");
        ini_set("smtp_port", 587);
        $this->headers = "MIME-Version: 1.0" . "\r\n";
        $this->headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    }

    public function setTo($to)
    {
        $this->to = $to;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function send()
    {
        if (empty($this->to) || empty($this->subject) || empty($this->message)) {
            return false; // In a real application, you should handle this case more gracefully.
        }

        $headers = $this->headers . "From: amisuusman@gmail.com\r\n";

        $success = mail($this->to, $this->subject, $this->message, $headers);
        return $success;
    }
}
