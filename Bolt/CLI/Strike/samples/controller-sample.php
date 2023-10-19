<?php

declare(strict_types=1);

/**
 * ===============================================
 * ==================           ==================
 * ****** {CLASSNAME}
 * ==================           ==================
 * ===============================================
 */

namespace Bolt\controllers;

use Bolt\Bolt\Controller;

class {CLASSNAME} extends Controller
{
    public function onConstruct(): void
    {
        // To add middleware, if middleware is for all the controller page, dont all the array. ['users'].
        $this->registerMiddleware(new AuthMiddleware(['users']));    
    }

    public function welcome()
    {
        $view = [
            'title' => 'Bolt Framework',
            'header' => 'Hello, User! Welcome to Bolt Framework',
            'text' => 'You are most welcome to our world.',
            'items' => ['Item 1', 'Item 2', 'Item 3'],
        ];

        $this->view->render("{VIEWPATH}", $view);
    }

    public function onConstruct(): void
    {   
    }

    /**
     * Sign Up method. Sample
     *
     * @param Request $request
     * @return void
     */
    public function signup(Request $request)
    {
        $user = new Users();

        if ($request->isPost()) {
            $user->setAllowedInsertParams([
                'username',
                'name',
                'phone',
                'email',
                'acl',
                'password'
            ]);
            $data = $request->getBody();
            // check for password validation.
            $user->passwordsMatchValidation($data['password'], $data['confirm_password']);
            if ($user->validate($data)) {
                // other method before saving.
                $data['password'] = hashPassword($data['password']);
                if ($user->insert($data)) {
                    FlashMessage::setMessage("User Created Successfully", FlashMessage::SUCCESS, ['role' => 'alert', 'style' => 'z-index: 9999;']);
                    redirect("/");
                }
            }
        }
        $view = [
            'errors' => $user->getErrors(),
            'user' => $user,
            'uuid' => generateUuidV4()
        ];

        $this->view->render("auth/signup", $view);
    }

    public function users()
    {
        // Initialize the BoltApi client with your API key and base URL
        $apiBaseUrl = 'http://localhost:3000';
        // $apiBaseUrl = 'https://jsonplaceholder.typicode.com';
        $boltApi = new BoltApi($apiBaseUrl);

        try {
            $endpoint = "/posts";
            $response = $boltApi->getWithRetry($endpoint);
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