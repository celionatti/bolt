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

class Mailer
{
    protected PHPMailer $mailer;
    protected array $config;
    protected ?string $template = null;
    protected array $templateData = [];
    protected string $viewsPath = '';
    protected bool $pretend = false;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->pretend = $this->config['pretend'] ?? false;
        $this->mailer = new PHPMailer(true);

        if (!$this->pretend) {
            $this->configureMailer();
        }
    }

    protected function configureMailer(): void
    {
        $this->mailer->CharSet = 'UTF-8';

        switch ($this->config['driver']) {
            case 'smtp':
                $this->configureSmtp();
                break;
            case 'sendmail':
                $this->configureSendmail();
                break;
            case 'mail':
                $this->configureMail();
                break;
            case 'log':
                // No configuration needed for log driver
                break;
            default:
                throw new RuntimeException("Unsupported mail driver: {$this->config['driver']}");
        }

        $this->setDefaultFrom();
    }

    protected function configureSmtp(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = $this->config['auth'] ?? true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = $this->config['encryption'] ?? 'tls';
        $this->mailer->Port = $this->config['port'];
    }

    protected function configureSendmail(): void
    {
        $this->mailer->isSendmail();
        $this->mailer->Sendmail = $this->config['sendmail'] ?? '/usr/sbin/sendmail -bs';
    }

    protected function configureMail(): void
    {
        $this->mailer->isMail();
    }

    protected function setDefaultFrom(): void
    {
        $from = $this->config['from'] ?? [];
        if (!empty($from['address'])) {
            $this->from($from['address'], $from['name'] ?? '');
        }
    }

    public function setViewsPath(string $path): self
    {
        $this->viewsPath = rtrim($path, '/') . '/';
        return $this;
    }

    public function pretend(bool $value = true): self
    {
        $this->pretend = $value;
        return $this;
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

    public function view(string $template, array $data = []): self
    {
        $this->template = $template;
        $this->templateData = $data;
        return $this;
    }

    public function html(string $content): self
    {
        $this->mailer->isHTML(true);
        $this->mailer->Body = $content;
        return $this;
    }

    public function text(string $content): self
    {
        $this->mailer->isHTML(false);
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
        if ($this->pretend || $this->config['driver'] === 'log') {
            return $this->logEmail();
        }

        try {
            if ($this->template) {
                $this->mailer->Body = $this->renderTemplate();
            }

            return $this->mailer->send();
        } catch (Exception $e) {
            throw new RuntimeException("Email sending failed: " . $this->mailer->ErrorInfo);
        }
    }

    protected function renderTemplate(): string
    {
        if (empty($this->viewsPath)) {
            throw new RuntimeException('Views path not configured');
        }

        $templateFile = $this->viewsPath . $this->template . '.php';

        if (!file_exists($templateFile)) {
            throw new RuntimeException("Email template not found: {$templateFile}");
        }

        extract($this->templateData);
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }

    protected function logEmail(): bool
    {
        $emailData = [
            'to' => $this->mailer->getToAddresses(),
            'cc' => $this->mailer->getCcAddresses(),
            'bcc' => $this->mailer->getBccAddresses(),
            'subject' => $this->mailer->Subject,
            'body' => $this->template ? $this->renderTemplate() : $this->mailer->Body,
            'pretend' => $this->pretend,
        ];

        error_log("Email sent (pretend mode):\n" . print_r($emailData, true));
        return true;
    }

    public function reset(): self
    {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
        $this->template = null;
        $this->templateData = [];
        return $this;
    }
}