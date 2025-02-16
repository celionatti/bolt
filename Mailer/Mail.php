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
    protected array $config;
    protected bool $pretend;

    public function __construct(array $config = [])
    {
        $this->config = $this->mergeConfig($config);
        $this->pretend = (bool)($this->config['pretend'] ?? false);
        $this->initializeMailer();
    }

    protected function mergeConfig(array $config): array
    {
        return array_merge([
            'driver' => bolt_env('MAIL_DRIVER', 'smtp'),
            'host' => bolt_env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => bolt_env('MAIL_PORT', 587),
            'username' => bolt_env('MAIL_USERNAME'),
            'password' => bolt_env('MAIL_PASSWORD'),
            'encryption' => bolt_env('MAIL_ENCRYPTION', 'tls'),
            'from' => [
                'address' => bolt_env('MAIL_FROM_ADDRESS', 'support@eventlyy.com.ng'),
                'name' => bolt_env('MAIL_FROM_NAME', 'Eventlyy'),
            ],
            'debug' => (bool)bolt_env('MAIL_DEBUG', false),
        ], $config);
    }

    protected function initializeMailer(): void
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->XMailer = ' '; // Remove X-Mailer header

        if ($this->config['driver'] === 'smtp') {
            $this->configureSmtp();
        } else {
            $this->mailer->isMail();
        }

        $this->setDefaultFrom();
    }

    protected function configureSmtp(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = $this->config['encryption'];
        $this->mailer->Port = $this->config['port'];
        $this->mailer->SMTPDebug = $this->config['debug'] ? 2 : 0;
    }

    protected function setDefaultFrom(): void
    {
        $this->from(
            $this->config['from']['address'],
            $this->config['from']['name']
        );
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

    public function template(string $path): self
    {
        $this->templatePath = $path;
        return $this;
    }

    public function send(): bool
    {
        if ($this->pretend) {
            $this->logEmail();
            return true;
        }

        try {
            if ($this->templatePath) {
                $this->renderTemplate();
            }

            if (empty($this->mailer->Body)) {
                throw new \RuntimeException('Email body is empty');
            }

            return $this->mailer->send();
        } catch (Exception $e) {
            throw new \RuntimeException("Email sending failed: " . $e->getMessage(), 0, $e);
        }
    }

    protected function logEmail(): void
    {
        $log = sprintf(
            "Pretend email sent:\nFrom: %s\nTo: %s\nSubject: %s\nBody: %s\n",
            $this->mailer->From,
            implode(', ', array_column($this->mailer->getToAddresses(), 0)),
            $this->mailer->Subject,
            $this->mailer->Body
        );
        error_log($log);
    }

    protected function renderTemplate(): void
    {
        if (!file_exists($this->templatePath)) {
            throw new \InvalidArgumentException("Template file not found: {$this->templatePath}");
        }

        extract($this->templateData, EXTR_SKIP);
        ob_start();
        include $this->templatePath;
        $content = ob_get_clean();

        $this->mailer->isHTML(true);
        $this->mailer->Body = $content;
        $this->mailer->AltBody = strip_tags($content);
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
