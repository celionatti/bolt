<?php

declare(strict_types=1);

/**
 * ===================================
 * Bolt - Api ========================
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


class Api
{
    private $client;
    private $baseUri;
    private $headers;
    private $cache;
    private $logger;

    public function __construct($baseUri, $headers = [], LoggerInterface $logger = null)
    {
        $this->baseUri = $baseUri;
        $this->headers = $headers;
        $this->cache = new FilesystemAdapter(); // Using Symfony's Filesystem Cache
        $this->logger = $logger;

        $handlerStack = \GuzzleHttp\HandlerStack::create();
        $handlerStack->push($this->retryMiddleware());
        $handlerStack->push($this->logMiddleware());

        $this->client = new Client([
            'base_uri' => $baseUri,
            'headers' => $headers,
            'handler' => $handlerStack,
            'timeout' => 10,
        ]);
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
        switch ($contentType) {
            case 'form_params':
                return ['form_params' => $data];
            case 'multipart':
                return ['multipart' => $data];
            case 'json':
            default:
                return ['json' => $data];
        }
    }

    private function request($method, $endpoint, $options = [])
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);
            return $this->handleResponse($response);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $this->handleResponse($e->getResponse());
            }
            return ['error' => $e->getMessage()];
        }
    }

    private function handleResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        return [
            'status_code' => $statusCode,
            'data' => $data,
        ];
    }

    private function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null,
            RequestException $exception = null
        ) {
            if ($retries >= 3) {
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
        });
    }

    private function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            if ($this->logger) {
                $this->logger->info('Sending request', ['request' => $request]);
            }
        }, function (RequestInterface $request, ResponseInterface $response) {
            if ($this->logger) {
                $this->logger->info('Received response', ['response' => $response]);
            }
        });
    }

    private function getCacheKey($method, $endpoint, $params = [])
    {
        return md5($method . $endpoint . json_encode($params));
    }
}