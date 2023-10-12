<?php

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class BoltApi
{
    protected $httpClient;
    // ...

    public function __construct($baseUrl, $apiKey = null)
    {
        // ...

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $this->buildHeaders(),
        ]);
    }

    public function getAsync($endpoint, $params = [])
    {
        return $this->httpClient->requestAsync('GET', $endpoint, ['query' => $params])->then(function ($response) {
            return $this->getData($response);
        });
    }

    public function postAsync($endpoint, $data)
    {
        return $this->httpClient->requestAsync('POST', $endpoint, [
            'json' => $data,
        ])->then(function ($response) {
            return $this->getData($response);
        });
    }

    protected function getData($response)
    {
        $responseBody = (string)$response->getBody();
        if ($response->getStatusCode() === 200) {
            return json_decode($responseBody, true);
        }
    }

    // ...
}
