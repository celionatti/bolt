<?php

declare(strict_types=1);

namespace Bolt\controllers;

use Bolt\Bolt\API\BoltApi;
use Bolt\Bolt\Controller;
use Bolt\models\Users;

class SiteController extends Controller
{
    public function welcome()
    {
        $data = [
            'title' => 'Hello World',
            'header' => 'Welcome To Bolt',
            'text' => 'You are most welcome to our world.',
            'greeting' => 'Hello, World!',
        ];

        $this->view->render("welcome", $data);
    }

    public function users()
    {
        // Initialize the BoltApi client with your API key and base URL
        $apiBaseUrl = 'http://localhost:3000';
        // $apiBaseUrl = 'https://jsonplaceholder.typicode.com';
        $boltApi = new BoltApi($apiBaseUrl);

        try {
            $endpoint = "/posts";
            $response = $boltApi->get($endpoint);
            $data = [
                "response" => $response,
                "title" => "JSON Placeholder Post Request API."
            ];
            $this->view->render("users", $data);
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function delete()
    {
        // Initialize the BoltApi client with your API key and base URL
        $apiBaseUrl = 'http://localhost:3000';
        $boltApi = new BoltApi($apiBaseUrl);

        try {
            $endpoint = "/posts/3";
            return $boltApi->delete($endpoint);
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function update()
    {
        // Initialize the BoltApi client with your API key and base URL
        $apiBaseUrl = 'http://localhost:3000';
        $boltApi = new BoltApi($apiBaseUrl);

        try {
            $data = [
                "title" => "Post title for Two updated",
                "body" => "Post Two updated Body content",
                "userId" => 1
            ];
            $endpoint = "/posts/2";
            return $boltApi->put($endpoint, $data);
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function create()
    {
        // Initialize the BoltApi client with your API key and base URL
        $apiBaseUrl = 'http://localhost:3000';
        $boltApi = new BoltApi($apiBaseUrl);

        try {
            $data = [
                "title" => "Post title for New Data",
                "body" => "Post for new Body content",
                "userId" => 2
            ];
            $endpoint = "/posts";
            return $boltApi->post($endpoint, $data);
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}


// https://my-json-server.typicode.com/typicode/demo/celionatti/bolt