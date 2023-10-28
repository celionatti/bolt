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
    const CONTINUE = 101;
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NO_CONTENT = 204;
    const FOUND = 302;
    const NOT_MODIFIED = 304;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENT_REDIRECT = 308;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const REQUEST_TIMEOUT = 408;
    const INTERNAL_SERVER_ERROR = 500;
    const BAD_GATEWAY = 502;
    const GATEWAY_TIMEOUT = 504;

    public $statusCode;
    public $statusText;
    public $headers = [];
    public $content;

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public static function getStatusText($statusCode)
    {
        $statusTexts = [
            self::CONTINUE => 'Continue',
            self::OK => 'OK',
            self::CREATED => 'Created',
            self::ACCEPTED => 'Accepted',
            self::NO_CONTENT => 'No Content',
            self::FOUND => 'Found',
            self::NOT_MODIFIED => 'Not Modified',
            self::TEMPORARY_REDIRECT => 'Temporary Redirect',
            self::PERMANENT_REDIRECT => 'Permanent Redirect',
            self::BAD_REQUEST => 'Bad Request',
            self::UNAUTHORIZED => 'Unauthorized',
            self::PAYMENT_REQUIRED => 'Payment Required',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Not Found',
            self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
            self::NOT_ACCEPTABLE => 'Not Acceptable',
            self::REQUEST_TIMEOUT => 'Request Timeout',
            self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
            self::BAD_GATEWAY => 'Bad Gateway',
            self::GATEWAY_TIMEOUT => 'Gateway Timeout',
            // Add more status codes and texts as needed
        ];

        return $statusTexts[$statusCode] ?? 'Unknown Status';
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    public function redirect($url, $statusCode = self::FOUND)
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        $this->send();
    }

    public function setJsonContent(array $data)
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
    }

    public function setPlainTextContent($text)
    {
        $this->setHeader('Content-Type', 'text/plain');
        $this->setContent($text);
    }

    public function setFileContent($filePath)
    {
        if (file_exists($filePath)) {
            $this->setContent(file_get_contents($filePath));
        } else {
            // Handle file not found error
            $this->setStatusCode(self::NOT_FOUND);
            $this->setContent('File not found');
        }
    }

    public function send()
    {
        // Send HTTP headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Set the HTTP response code and status text
        header("HTTP/1.1 {$this->statusCode} {$this->statusText}");

        // Output the response content
        echo $this->content;
    }
}
