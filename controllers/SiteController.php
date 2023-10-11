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
        // $data = [
        //     'title' => 'Welcome to My Website',
        //     'header' => 'Hello, Blade!',
        //     'items' => ['Item 1', 'Item 2', 'Item 3'],
        // ];
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
        $boltApi = new BoltApi($apiKey = null, $apiBaseUrl);

        try {
            $endpoint = "/todos";
            $response = $boltApi->sendRequest($endpoint);
            dd($response);
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}
