<?php

declare(strict_types=1);

/**
 * ========================================================
 * =====================            =======================
 * Bolt - MailService Class
 * =====================            =======================
 * ========================================================
 */

namespace celionatti\Bolt\Mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Closure;

class MailService
{
    protected PHPMailer $mailer;
    protected ?string $templatePath = null;
    protected array $templateData = [];

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureDefaultSettings();
    }

    protected function configureDefaultSettings(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = env('MAIL_HOST', 'smtp.gmail.com');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = env('MAIL_USERNAME');
        $this->mailer->Password = env('MAIL_PASSWORD');
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = env('MAIL_PORT', 587);
    }

    public function from(string $email, string $name = ''): self
    {
        $this->mailer->setFrom($email, $name);
        return $this;
    }

    public function to($email, string $name = ''): self
    {
        $this->mailer->addAddress($email, $name);
        return $this;
    }

    public function cc($email, string $name = ''): self
    {
        $this->mailer->addCC($email, $name);
        return $this;
    }

    public function bcc($email, string $name = ''): self
    {
        $this->mailer->addBCC($email, $name);
        return $this;
    }

    public function subject(string $subject): self
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    public function template(string $path, array $data = []): self
    {
        $this->templatePath = $path;
        $this->templateData = $data;
        return $this;
    }

    public function html(string $content): self
    {
        $this->mailer->isHTML(true);
        $this->mailer->Body = $content;
        return $this;
    }

    public function attach(string $path, string $name = ''): self
    {
        $this->mailer->addAttachment($path, $name);
        return $this;
    }

    public function send(): bool
    {
        try {
            if ($this->templatePath) {
                $this->renderTemplate();
            }

            return $this->mailer->send();
        } catch (Exception $e) {
            // Log the error or handle as needed
            throw new \Exception("Email sending failed: " . $this->mailer->ErrorInfo);
        }
    }

    protected function renderTemplate(): void
    {
        // Simple template rendering with extract
        $data = $this->templateData;
        ob_start();
        extract($data);
        require $this->templatePath;
        $content = ob_get_clean();

        $this->mailer->isHTML(true);
        $this->mailer->Body = $content;
    }

    public function reset(): self
    {
        $this->mailer = new PHPMailer(true);
        $this->configureDefaultSettings();
        $this->templatePath = null;
        $this->templateData = [];
        return $this;
    }
}
