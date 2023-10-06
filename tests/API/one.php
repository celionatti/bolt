<?php

class ApiIntegration
{
    private $apiKey;
    private $apiBaseUrl;

    public function __construct($apiKey, $apiBaseUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function sendRequest($endpoint, $method = 'GET', $data = [])
    {
        $url = $this->apiBaseUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

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
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('API Request Error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}


/**
 * Usage
 */

$apiKey = 'your-api-key';
$apiBaseUrl = 'https://api.example.com/';

$api = new ApiIntegration($apiKey, $apiBaseUrl);

// Example GET request
$response = $api->sendRequest('products');

// Example POST request
$data = ['name' => 'New Product', 'price' => 19.99];
$response = $api->sendRequest('products', 'POST', $data);
