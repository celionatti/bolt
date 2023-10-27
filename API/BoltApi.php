<?php

declare(strict_types=1);

/**
 * ===================================
 * Bolt - BoltApi ====================
 * ===================================
 */

namespace celionatti\Bolt\API;

use Exception;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\StreamWrapper;
use celionatti\Bolt\BoltException\BoltException;


class BoltApi
{
    protected $httpClient;
    protected $baseUrl;
    protected $apiKey;

    public function __construct($baseUrl, $apiKey = null)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;

        $this->httpClient = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl,
            'headers' =>  $this->buildHeaders(),
        ]);
    }

    public function get($endpoint, $params = [])
    {
        try {
            $response = $this->httpClient->get($endpoint, ['query' => $params]);
            $data = $this->getData($response);
            return $data;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }

    public function getAsync($endpoint, $params = [])
    {
        return $this->httpClient->requestAsync('GET', $endpoint, ['query' => $params])->then(function ($response) {
            return $this->getData($response);
        });
    }

    public function getWithRetry($endpoint, $params = [], $retryCount = 3, $retryDelay = 1)
    {
        $attempts = 0;

        while ($attempts < $retryCount) {
            try {
                $response = $this->httpClient->get($endpoint, ['query' => $params]);

                return $this->getData($response);
            } catch (BoltException $e) {
                // Handle any exceptions if needed
                bolt_die($e);
            }

            $attempts++;
            if ($attempts < $retryCount) {
                sleep($retryDelay);
            }
        }

        throw new Exception("Request failed after $retryCount attempts.");
    }

    public function post($endpoint, $data)
    {
        try {
            $response = $this->httpClient->post($endpoint, [
                'json' => $data,
            ]);
            $data = $this->getData($response);
            return $data;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }

    public function postAsync($endpoint, $data)
    {
        return $this->httpClient->requestAsync('POST', $endpoint, [
            'json' => $data,
        ])->then(function ($response) {
            return $this->getData($response);
        });
    }

    public function postWithRetry($endpoint, $data, $retryCount = 3, $retryDelay = 1)
    {
        $attempts = 0;

        while ($attempts < $retryCount) {
            try {
                $response = $this->httpClient->post($endpoint, ['json' => $data]);

                return $this->getData($response);
            } catch (BoltException $e) {
                // Handle any exceptions if needed
                bolt_die($e);
            }

            $attempts++;
            if ($attempts < $retryCount) {
                sleep($retryDelay);
            }
        }

        throw new Exception("Request failed after $retryCount attempts.");
    }

    public function put($endpoint, $data)
    {
        try {
            $response = $this->httpClient->put($endpoint, [
                'json' => $data,
            ]);
            $data = $this->getData($response);
            return $data;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }

    public function putWithRetry($endpoint, $data, $retryCount = 3, $retryDelay = 1)
    {
        $attempts = 0;

        while ($attempts < $retryCount) {
            try {
                $response = $this->httpClient->put($endpoint, ['json' => $data]);

                return $this->getData($response);
            } catch (BoltException $e) {
                // Handle any exceptions if needed
                bolt_die($e);
            }

            $attempts++;
            if ($attempts < $retryCount) {
                sleep($retryDelay);
            }
        }

        throw new Exception("Request failed after $retryCount attempts.");
    }

    public function delete($endpoint)
    {
        try {
            $response = $this->httpClient->delete($endpoint);
            // return $response->getStatusCode(); // successful deletion
            return $response->getStatusCode() === 204 || $response->getStatusCode() === 200; // 204 indicates a successful deletion
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }

    public function deleteWithRetry($endpoint, $retryCount = 3, $retryDelay = 1)
    {
        $attempts = 0;

        while ($attempts < $retryCount) {
            try {
                $response = $this->httpClient->delete($endpoint);

                return $response->getStatusCode() === 204 || $response->getStatusCode() === 200;
            } catch (BoltException $e) {
                // Handle any exceptions if needed
                bolt_die($e);
            }

            $attempts++;
            if ($attempts < $retryCount) {
                sleep($retryDelay);
            }
        }

        throw new Exception("Request failed after $retryCount attempts.");
    }

    public function getData($response)
    {
        $responseBody = (string)$response->getBody();

        if ($response instanceof Response && $response->getStatusCode() === 200)
            return json_decode($responseBody, true);
    }

    public function getAllPaginatedData($endpoint, $params = [])
    {
        $allData = [];

        $page = 1;
        $perPage = 100; // Adjust this as per your API's pagination settings.

        do {
            $params['page'] = $page;
            $params['per_page'] = $perPage;

            $response = $this->get($endpoint, $params);

            if (empty($response)) {
                break; // No more data to fetch.
            }

            $allData = array_merge($allData, $response);

            $page++; // Move to the next page.

        } while (count($response) === $perPage);

        return $allData;
    }

    public function batchCreate($endpoint, array $dataItems)
    {
        $responses = [];

        foreach ($dataItems as $data) {
            try {
                $response = $this->post($endpoint, $data);
                $responses[] = $response;
            } catch (Exception $e) {
                $responses[] = ['error' => $e->getMessage()];
            }
        }

        return $responses;
    }

    public function requestWithRetry($method, $endpoint, $data = null, $retryCount = 3)
    {
        $attempts = 0;

        while ($attempts < $retryCount) {
            try {
                if ($method === 'get') {
                    $response = $this->httpClient->get($endpoint, ['query' => $data]);
                } elseif ($method === 'post') {
                    $response = $this->httpClient->post($endpoint, ['json' => $data]);
                } elseif ($method === 'put') {
                    $response = $this->httpClient->put($endpoint, ['json' => $data]);
                } elseif ($method === 'delete') {
                    $response = $this->httpClient->delete($endpoint);
                }

                // Check if the response is successful
                if ($response instanceof Response && $response->getStatusCode() === 200) {
                    return json_decode((string)$response->getBody(), true);
                }
            } catch (BoltException $e) {
                // Handle any exceptions if needed
                bolt_die($e);
            }

            $attempts++;
            if ($attempts < $retryCount) {
                sleep(1); // You can adjust the sleep duration between retries.
            }
        }

        throw new Exception("Request failed after $retryCount attempts.");
    }

    public function buildHeaders()
    {
        $headers = ['Accept' => 'application/json'];

        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        return $headers;
    }

    public function uploadFile($endpoint, $filePath)
    {
        try {
            $response = $this->httpClient->post($endpoint, [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => Utils::tryFopen($filePath, 'r'),
                    ],
                ],
            ]);
            $data = $this->getData($response);
            return $data;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }

    public function downloadFile($endpoint, $localFilePath)
    {
        try {
            $response = $this->httpClient->get($endpoint);
            $stream = StreamWrapper::getResource($response->getBody());
            $fileHandle = fopen($localFilePath, 'w');
            stream_copy_to_stream($stream, $fileHandle);
            fclose($fileHandle);
            return true; // Successful download
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }
}
