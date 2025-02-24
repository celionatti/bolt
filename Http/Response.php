<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Response Class ===========
 * =================================
 */

namespace celionatti\Bolt\Http;

use celionatti\Bolt\Exceptions\HttpException;
use celionatti\Bolt\Security\SecurityHeaders;

class Response
{
    private array $headers = [];
    private array $cookies = [];
    private int $statusCode = 200;
    private string $statusText = 'OK';
    private $body;
    private bool $sent = false;
    private bool $compressionEnabled = false;

    private const STATUS_TEXTS = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        415 => 'Unsupported Media Type',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    public function __construct($body = '', int $statusCode = 200, array $headers = [])
    {
        $this->body = $body;
        $this->setStatusCode($statusCode);
        $this->setDefaultSecurityHeaders();
        $this->setMultipleHeaders($headers);
    }

    public function setHeader(string $name, string $value, bool $replace = true): self
    {
        $normalized = strtolower($name);

        if ($replace || !isset($this->headers[$normalized])) {
            $this->headers[$normalized] = [$value];
        } else {
            $this->headers[$normalized][] = $value;
        }

        return $this;
    }

    public function removeHeader(string $name): self
    {
        unset($this->headers[strtolower($name)]);
        return $this;
    }

    public function setCookie(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = true,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $this->validateCookieParams($name, $value);

        $this->cookies[] = [
            'name' => $name,
            'value' => rawurlencode($value),
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ];

        return $this;
    }

    public function deleteCookie(string $name): self
    {
        return $this->setCookie($name, '', time() - 3600);
    }

    public function setStatusCode(int $code, string $text = null): self
    {
        if ($code < 100 || $code > 599) {
            throw new HttpException("Invalid HTTP status code: {$code}", 500);
        }

        $this->statusCode = $code;
        $this->statusText = $text ?? self::STATUS_TEXTS[$code] ?? 'unknown status';
        return $this;
    }

    public function json($data, int $status = 200, int $options = 0): self
    {
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->setStatusCode($status);
        $this->body = json_encode($data, $options | JSON_THROW_ON_ERROR);
        return $this;
    }

    public function redirect(string $url, int $status = 302): self
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new HttpException("Invalid redirect URL: {$url}", 500);
        }

        $this->setHeader('Location', $url);
        return $this->setStatusCode($status);
    }

    public function file(string $filePath, string $downloadName = null, bool $asAttachment = true): self
    {
        if (!is_readable($filePath) || !is_file($filePath)) {
            throw new HttpException("File not found: {$filePath}", 404);
        }

        $this->body = static function () use ($filePath) {
            readfile($filePath);
        };

        $this->setHeader('Content-Type', mime_content_type($filePath));
        $this->setHeader('Content-Length', (string)filesize($filePath));

        if ($asAttachment) {
            $name = $downloadName ?? basename($filePath);
            $this->setHeader('Content-Disposition', 'attachment; filename="' . rawurlencode($name) . '"');
        }

        return $this;
    }

    public function html(string $content, int $status = 200): self
    {
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->setStatusCode($status);
        $this->body = $content;
        return $this;
    }

    public function send(): void
    {
        if ($this->sent) {
            throw new HttpException('Response already sent', 500);
        }

        $this->sent = true;

        if (!headers_sent()) {
            $this->sendHeaders();
            $this->sendCookies();
        }

        $this->sendBody();
    }

    public function enableCompression(): self
    {
        if (extension_loaded('zlib') && !ob_start('ob_gzhandler')) {
            throw new HttpException('Failed to enable output compression', 500);
        }

        $this->compressionEnabled = true;
        return $this;
    }

    private function sendHeaders(): void
    {
        header("HTTP/1.1 {$this->statusCode} {$this->statusText}", true, $this->statusCode);

        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }
    }

    private function sendCookies(): void
    {
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                [
                    'expires' => $cookie['expire'],
                    'path' => $cookie['path'],
                    'domain' => $cookie['domain'],
                    'secure' => $cookie['secure'],
                    'httponly' => $cookie['httponly'],
                    'samesite' => $cookie['samesite'],
                ]
            );
        }
    }

    private function sendBody(): void
    {
        if ($this->body instanceof \Closure) {
            ($this->body)();
        } else {
            echo $this->body;
        }

        if ($this->compressionEnabled) {
            ob_end_flush();
        }
    }

    private function setDefaultSecurityHeaders(): void
    {
        $this->setMultipleHeaders(SecurityHeaders::getDefaults());
    }

    private function setMultipleHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    private function validateCookieParams(string $name, string $value): void
    {
        if (preg_match('/[=,; \t\r\n\013\014]/', $name)) {
            throw new HttpException("Invalid cookie name: {$name}", 500);
        }

        if (strlen($value) > 4096) {
            throw new HttpException("Cookie value too long: {$name}", 500);
        }
    }

    public function __destruct()
    {
        if (!$this->sent) {
            $this->send();
        }
    }
}
