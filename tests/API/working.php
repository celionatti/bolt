<?php

declare(strict_types=1);

/**
 * ===================================
 * Bolt - BoltApi ====================
 * ===================================
 */

namespace Bolt\Bolt\API;

use Exception;


class BoltApi
{
    private $apiKey;
    private $apiBaseUrl;
    private $headers;

    public function __construct($apiKey = null, $apiBaseUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
        if (null === $this->apiKey) {
            $this->headers = [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ];
        }
        $this->headers = [
            'Content-Type: application/json',
        ];
    }

    public function sendRequest($endpoint, $method = 'GET', $data = [])
    {
        $url = $this->apiBaseUrl . '/' . ltrim($endpoint, '/');

        $ch = curl_init();

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('API Request Error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception('API Request Failed with HTTP Status Code: ' . $httpCode);
        }

        return json_decode($response, true);
    }

    public function paginatedRequest($endpoint, $page = 1, $perPage = 10)
    {
        $queryParams = ['page' => $page, 'per_page' => $perPage];
        $url = $endpoint . '?' . http_build_query($queryParams);

        return $this->sendRequest($url);
    }

    public function putRequest($endpoint, $data = [])
    {
        return $this->sendRequest($endpoint, 'PUT', $data);
    }

    public function deleteRequest($endpoint)
    {
        return $this->sendRequest($endpoint, 'DELETE');
    }

    public function setCustomHeaders(array $customHeaders)
    {
        $this->headers = array_merge($this->headers, $customHeaders);
    }

    public function extractPropertyFromResponse($endpoint, $property)
    {
        $response = $this->sendRequest($endpoint);
        return $response[$property] ?? null;
    }

    public function getRequestWithQueryParams($endpoint, array $queryParams)
    {
        $url = $endpoint . '?' . http_build_query($queryParams);
        return $this->sendRequest($url);
    }

    public function batchRequests(array $endpoints)
    {
        $responses = [];

        foreach ($endpoints as $endpoint) {
            $responses[] = $this->sendRequest($endpoint);
        }

        return $responses;
    }
}
