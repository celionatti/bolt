<?php

declare(strict_types=1);

/**
 * ===================================
 * Bolt - API ========================
 * ===================================
 */

namespace celionatti\Bolt\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class API
{
    private $client;
    private $baseUri;
    private $headers;
    private $cache;
    private $logger;
    private $defaultTimeout;

    public function __construct($baseUri, $headers = [], LoggerInterface $logger = null, $defaultTimeout = 10)
    {
        $this->baseUri = $baseUri;
        $this->headers = $headers;
        $this->cache = new FilesystemAdapter(); // Symfony's Filesystem Cache
        $this->logger = $logger;
        $this->defaultTimeout = $defaultTimeout;

        $handlerStack = \GuzzleHttp\HandlerStack::create();
        $handlerStack->push($this->retryMiddleware());
        $handlerStack->push($this->logMiddleware());

        $this->client = new Client([
            'base_uri' => $baseUri,
            'handler' => $handlerStack,
            'timeout' => $defaultTimeout,
        ]);
    }

    public function setHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    public function get($endpoint, $queryParams = [], $cacheTtl = 3600)
    {
        $cacheKey = $this->getCacheKey('GET', $endpoint, $queryParams);
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($endpoint, $queryParams, $cacheTtl) {
            $item->expiresAfter($cacheTtl);
            return $this->request('GET', $endpoint, ['query' => $queryParams]);
        });
    }

    public function post($endpoint, $data = [], $contentType = 'json')
    {
        return $this->request('POST', $endpoint, $this->prepareOptions($data, $contentType));
    }

    public function put($endpoint, $data = [], $contentType = 'json')
    {
        return $this->request('PUT', $endpoint, $this->prepareOptions($data, $contentType));
    }

    public function delete($endpoint, $data = [], $contentType = 'json')
    {
        return $this->request('DELETE', $endpoint, $this->prepareOptions($data, $contentType));
    }

    private function prepareOptions($data, $contentType)
    {
        $options = ['headers' => $this->headers];

        switch ($contentType) {
            case 'form_params':
                $options['form_params'] = $data;
                break;
            case 'multipart':
                $options['multipart'] = $data;
                break;
            case 'json':
            default:
                $options['json'] = $data;
                break;
        }

        return $options;
    }

    private function request($method, $endpoint, $options = [])
    {
        try {
            $options['headers'] = array_merge($this->headers, $options['headers'] ?? []);
            $response = $this->client->request($method, $endpoint, $options);

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $this->handleErrorResponse($e->getResponse());
            }
            $this->logger?->error('API Request Exception', ['message' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            $this->logger?->error('Unexpected API Error', ['message' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    private function handleResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaderLine('Content-Type');
        $body = (string)$response->getBody();

        $data = match (true) {
            str_contains($contentType, 'application/json') => json_decode($body, true),
            str_contains($contentType, 'application/xml') => simplexml_load_string($body),
            default => $body,
        };

        return [
            'status_code' => $statusCode,
            'data' => $data,
        ];
    }

    private function handleErrorResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();

        return [
            'error' => true,
            'status_code' => $statusCode,
            'body' => $body,
        ];
    }

    private function retryMiddleware($maxRetries = 3, $retryDelay = 1000)
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null,
            RequestException $exception = null
        ) use ($maxRetries) {
            if ($retries >= $maxRetries) {
                return false;
            }

            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                $statusCode = $response->getStatusCode();
                if ($statusCode >= 500 || $statusCode === 429) {
                    return true;
                }
            }

            return false;
        }, function () use ($retryDelay) {
            return $retryDelay;
        });
    }

    private function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            $start = microtime(true);
            $this->logger?->info('API Request Started', ['request' => $request]);
            $options['start_time'] = $start;
        }, function (RequestInterface $request, ResponseInterface $response) {
            $end = microtime(true);
            $duration = $end - $options['start_time'] ?? 0;
            $this->logger?->info('API Request Completed', [
                'response' => $response,
                'duration' => $duration,
            ]);
        });
    }

    private function getCacheKey($method, $endpoint, $params = [])
    {
        return md5($method . $endpoint . json_encode($params));
    }
}
