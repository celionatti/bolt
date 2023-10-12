<?php

require 'vendor/autoload.php'; // Include GuzzleHTTP library

class BaseAPIClient
{
    protected $httpClient;
    protected $baseUrl;
    protected $apiKey;
    
    public function __construct($baseUrl, $apiKey = null)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        
        $headers = ['Content-Type' => 'application/json'];
        
        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }
        
        $this->httpClient = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl,
            'headers' => $headers,
        ]);
    }

    public function get($endpoint, $params = [])
    {
        try {
            $response = $this->httpClient->get($endpoint, ['query' => $params]);
            $data = json_decode($response->getBody(), true);
            return $data;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }

    public function post($endpoint, $data)
    {
        try {
            $response = $this->httpClient->post($endpoint, [
                'json' => $data,
            ]);
            $data = json_decode($response->getBody(), true);
            return $data;
        } catch (\GuzzleHttp\Exception.RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }

    public function put($endpoint, $data)
    {
        try {
            $response = $this->httpClient->put($endpoint, [
                'json' => $data,
            ]);
            $data = json_decode($response->getBody(), true);
            return $data;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }

    public function delete($endpoint)
    {
        try {
            $response = $this->httpClient->delete($endpoint);
            return $response->getStatusCode() === 204; // 204 indicates a successful deletion
        } catch (\GuzzleHttp\Exception.RequestException $e) {
            throw new Exception("Error: " . $e->getResponse()->getBody());
        }
    }
}
