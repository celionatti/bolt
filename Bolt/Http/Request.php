<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Request Class ===========
 * =================================
 */

 namespace Bolt\Bolt\Http;

class Request
{
    private array $parameters = [];

    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }

    public function method(): string
    {
        return $_POST['_method'] ?? strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isPut(): bool
    {
        return $this->method() === 'PUT';
    }

    public function isPatch(): bool
    {
        return $this->method() === 'PATCH';
    }

    public function isDelete(): bool
    {
        return $this->method() === 'DELETE';
    }

    /**
     * get a value from the GET variable
     *
     */
    public function get(string $key = '', mixed $default = ''): mixed
    {

        if (empty($key)) {
            return $this->esc($_GET);
        } elseif (isset($_GET[$key])) {
            return $this->esc($_GET[$key]);
        }

        return $this->esc($default);
    }

    /**
     * get a value from the POST variable
     *
     */
    public function post(string $key = '', mixed $default = ''): mixed
    {

        if (empty($key)) {
            return $this->esc($_POST);
        } elseif (isset($_POST[$key])) {
            return $this->esc($_POST[$key]);
        }

        return $this->esc($default);
    }

    /**
     * get a value from the FILES variable
     *
     */
    public function files(string $key = '', mixed $default = ''): mixed
    {

        if (empty($key)) {
            return $_FILES;
        } elseif (isset($_FILES[$key])) {
            return $_FILES[$key];
        }

        return $default;
    }

    public function getBody(): array
    {
        $body = [];
        if ($this->isGet() || $this->isDelete()) {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->isPost() || $this->isPatch() || $this->isPut() || $this->isDelete()) {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $body;
    }

    public function getData($input = false): false|array|string
    {
        if (!$input) {
            $data = [];
            foreach ($_REQUEST as $field => $value) {
                $data[$field] = self::sanitize($value);
            }
            return $data;
        }
        return array_key_exists($input, $_REQUEST) ? self::sanitize($_REQUEST[$input]) : false;
    }

    public static function sanitize($dirty): string
    {
        return htmlspecialchars($dirty);
    }

    public function esc($str): string
    {
        return htmlspecialchars($str);
    }

    /**
     * @param $params
     * @return self
     */
    public function setParameters($parameters): Request
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function getParameter($parameter, $default = null)
    {
        return $this->parameters[$parameter] ?? $default;
    }
}