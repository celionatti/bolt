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
        $apiBaseUrl = 'https://jsonplaceholder.typicode.com';
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
}
