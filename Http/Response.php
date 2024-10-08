<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Response Class ===========
 * =================================
 */

namespace celionatti\Bolt\Http;

class Response
{
    protected $headers = [];
    protected $cookies = [];
    protected $statusCode = 200;
    protected $statusText = 'OK';
    protected $body;

    protected static $statusTexts = [
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
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    public function __construct(string $body = '', int $statusCode = 200, array $headers = [])
    {
        $this->body = $body;
        $this->setStatusCode($statusCode);
        $this->headers = $headers;
        $this->cookies = [];
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        if (!isset($this->headers[$name])) {
            $this->headers[$name] = [];
        }
        $this->headers[$name][] = $value;
        return $this;
    }

    public function removeHeader(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    public function getHeader(string $name)
    {
        return $this->headers[$name] ?? null;
    }

    public function setCookie(string $name, string $value, int $expire = 0, string $path = '', string $domain = '', bool $secure = false, bool $httponly = false): self
    {
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
        ];
        return $this;
    }

    public function setStatusCode(int $code, string $text = null): self
    {
        $this->statusCode = $code;
        $this->statusText = $text ?? (self::$statusTexts[$code] ?? 'unknown status');
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function appendBody(string $content): self
    {
        $this->body .= $content;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function send(): void
    {
        // Send headers
        if (!headers_sent()) {
            header(sprintf('HTTP/1.1 %d %s', $this->statusCode, $this->statusText));

            foreach ($this->headers as $name => $values) {
                if (is_array($values)) {
                    foreach ($values as $value) {
                        header(sprintf('%s: %s', $name, $value), false);
                    }
                } else {
                    header(sprintf('%s: %s', $name, $values));
                }
            }

            foreach ($this->cookies as $cookie) {
                setcookie(
                    $cookie['name'],
                    $cookie['value'],
                    $cookie['expire'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httponly']
                );
            }
        }

        // Send body
        echo $this->body;
    }

    public function json($data, int $status = 200, array $headers = []): self
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->setStatusCode($status);
        $this->setBody(json_encode($data));

        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    public function redirect(string $url, int $status = 302): self
    {
        $this->setStatusCode($status);
        $this->setHeader('Location', $url);
        return $this;
    }

    public function file(string $filePath, string $fileName = '', string $mimeType = 'application/octet-stream'): self
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found.');
        }

        if (empty($fileName)) {
            $fileName = basename($filePath);
        }

        $this->setHeader('Content-Description', 'File Transfer');
        $this->setHeader('Content-Type', $mimeType);
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->setHeader('Expires', '0');
        $this->setHeader('Cache-Control', 'must-revalidate');
        $this->setHeader('Pragma', 'public');
        $this->setBody(file_get_contents($filePath));

        return $this;
    }

    public function html(string $html, int $status = 200): self
    {
        $this->setHeader('Content-Type', 'text/html');
        $this->setStatusCode($status);
        $this->setBody($html);
        return $this;
    }

    public function clearHeaders(): self
    {
        $this->headers = [];
        return $this;
    }

    public function setMultipleHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }

    public static function instance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
}
