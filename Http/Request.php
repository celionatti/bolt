<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Request Class ===========
 * =================================
 */

namespace celionatti\Bolt\Http;

use celionatti\Bolt\Validation\Validator;

class Request
{
    protected $headers = [];
    protected $queryParams = [];
    protected $bodyParams = [];
    protected $serverParams = [];
    protected $cookies = [];
    protected $files = [];
    protected $method;
    protected $path;
    protected $body;
    protected $errors = [];

    public function __construct()
    {
        $this->headers = $this->parseHeaders();
        $this->queryParams = $this->sanitizeArray($_GET);
        $this->body = file_get_contents('php://input') ?: '';
        $this->bodyParams = $this->sanitizeArray($this->parseBody());
        $this->serverParams = $_SERVER;
        $this->cookies = $this->sanitizeArray($_COOKIE);
        $this->files = $_FILES;
        $this->method = $this->detectMethod();
        $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function validate(array $rules, array $data = null): bool
    {
        $dataToValidate = $data ?? $this->bodyParams;
        $validator = new Validator($dataToValidate, $rules);
        if ($validator->fails()) {
            $this->errors = $validator->errors();
            return false;
        }
        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    protected function parseBody(): array
    {
        if (in_array($this->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $this->isFormData()) {
            parse_str($this->body, $bodyParams);
            return $bodyParams;
        }
        if ($this->isJson()) {
            return json_decode($this->body, true);
        }
        return [];
    }

    protected function detectMethod(): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $overrideMethod = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            if (in_array($overrideMethod, ['PUT', 'DELETE', 'PATCH'])) {
                return $overrideMethod;
            }
        }
        return $method;
    }

    public function isJson(): bool
    {
        return isset($this->headers['Content-Type']) && strpos($this->headers['Content-Type'], 'application/json') !== false;
    }

    public function isFormData(): bool
    {
        return isset($this->headers['Content-Type']) && strpos($this->headers['Content-Type'], 'application/x-www-form-urlencoded') !== false;
    }

    public function getHeader($name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getQueryParam($name, $default = null)
    {
        return $this->sanitize($this->queryParams[$name] ?? $default);
    }

    public function getBodyParam($name, $default = null)
    {
        return $this->sanitize($this->bodyParams[$name] ?? $default);
    }

    public function getServerParam($name, $default = null)
    {
        return $this->serverParams[$name] ?? $default;
    }

    public function getCookie($name, $default = null)
    {
        return $this->sanitize($this->cookies[$name] ?? $default);
    }

    public function getFile($name)
    {
        return $this->files[$name] ?? null;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function isAjax(): bool
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }

    public function isSecure(): bool
    {
        return (!empty($this->serverParams['HTTPS']) && $this->serverParams['HTTPS'] !== 'off')
            || $this->serverParams['SERVER_PORT'] == 443;
    }

    public function getIp(): ?string
    {
        return $this->serverParams['REMOTE_ADDR'] ?? null;
    }

    public function getUserAgent(): ?string
    {
        return $this->getHeader('User-Agent');
    }

    public function getReferer(): ?string
    {
        return $this->getHeader('Referer');
    }

    public function is($pattern): bool
    {
        $path = $this->getPath();
        $pattern = '/' . trim($pattern, '/');

        // Handle wildcard matching for simplicity
        if (strpos($pattern, '*') !== false) {
            $regex = str_replace('\*', '.*', preg_quote($pattern, '/'));
            return preg_match('/^' . $regex . '$/', $path);
        }

        return $path === $pattern;
    }

    public function with($key, $value)
    {
        // Add flash message or session data
        $_SESSION[$key] = $value;
        return $this;
    }

    public function back()
    {
        $_SERVER['HTTP_REFERER'] ?? '/';
        return $this;
    }

    // protected function sanitize($data)
    // {
    //     if (is_array($data)) {
    //         return array_map([$this, 'sanitize'], $data);
    //     }

    //     return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    // }

    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        // Advanced sanitization for specific data types
        if (is_numeric($data)) {
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }

        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }

    protected function sanitizeArray(array $data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->sanitize($value);
        }

        return $data;
    }

    public static function instance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    public function get($name, $default = null)
    {
        if (isset($this->bodyParams[$name])) {
            return $this->getBodyParam($name, $default);
        }

        if (isset($this->queryParams[$name])) {
            return $this->getQueryParam($name, $default);
        }

        if (isset($this->serverParams[$name])) {
            return $this->getServerParam($name, $default);
        }

        if (isset($this->headers[$name])) {
            return $this->getHeader($name);
        }

        return $default;
    }

    public function set($name, $value): void
    {
        if (isset($this->bodyParams[$name])) {
            $this->bodyParams[$name] = $this->sanitize($value);
        } elseif (isset($this->queryParams[$name])) {
            $this->queryParams[$name] = $this->sanitize($value);
        } elseif (isset($this->serverParams[$name])) {
            $this->serverParams[$name] = $value;
        } elseif (isset($this->headers[$name])) {
            $this->headers[$name] = $value;
        } elseif (isset($this->cookies[$name])) {
            $this->cookies[$name] = $this->sanitize($value);
        } elseif (isset($this->files[$name])) {
            $this->files[$name] = $value;
        } else {
            $this->bodyParams[$name] = $this->sanitize($value);
        }
    }

    public function validateFile($name, $allowedTypes = ['image/jpeg', 'image/png']): bool
    {
        if (!isset($this->files[$name])) {
            $this->errors[$name] = "File not found.";
            return false;
        }

        $file = $this->files[$name];
        if (!in_array($file['type'], $allowedTypes)) {
            $this->errors[$name] = "Invalid file type.";
            return false;
        }

        return true;
    }

    public function loadData(): array
    {
        return $this->sanitize($_POST);
    }

    public function getMergeData(): array
    {
        return $this->sanitize(array_merge($this->queryParams, $this->bodyParams, $_POST));
    }
}
