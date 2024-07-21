<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Request Class ===========
 * =================================
 */

 namespace celionatti\Bolt\Http;

class Request
{
    protected $_request;
    protected $_method;
    protected $_headers;

    private array $parameters = [];

    public function __construct()
    {
        $this->_request = $_REQUEST; // You can use $_GET, $_POST, or other specific superglobals as needed
        $this->_method = $_SERVER['REQUEST_METHOD'];
        $this->_headers = getallheaders();
    }

    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }

    public function getMethod(): string
    {
        return $_POST['_method'] ?? strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isPut(): bool
    {
        return $this->getMethod() === 'PUT';
    }

    public function isPatch(): bool
    {
        return $this->getMethod() === 'PATCH';
    }

    public function isDelete(): bool
    {
        return $this->getMethod() === 'DELETE';
    }

    public function getBody(): array
    {
        $body = [];
        if ($this->isGet() || $this->isDelete()) {
            foreach ($this->_request as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->isPost() || $this->isPatch() || $this->isPut() || $this->isDelete()) {
            foreach ($this->_request as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $body;
    }

    public function getData($input = false): false|array|string
    {
        if (!$input) {
            $data = [];
            foreach ($this->_request as $field => $value) {
                $data[$field] = self::sanitize($value);
            }
            return $data;
        }
        return array_key_exists($input, $this->_request) ? self::sanitize($this->_request[$input]) : false;
    }

    /**
     * Check if the current path matches the given pattern.
     *
     * @param string $pattern
     * @return bool
     */
    public function is(string $pattern): bool
    {
        $path = $this->getPath();

        // Perform a simple string comparison to check if the path matches the pattern
        return trim($path, '/') === $pattern;
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

    public static function sanitize($dirty): string
    {
        return htmlspecialchars($dirty);
    }

    protected function esc($str): string
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