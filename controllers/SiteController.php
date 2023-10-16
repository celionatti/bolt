<?php

declare(strict_types=1);

namespace Bolt\controllers;

use Bolt\Bolt\API\BoltApi;
use Bolt\Bolt\Controller;
use Bolt\Bolt\Http\Request;
use Bolt\middlewares\AuthMiddleware;
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

    public function users(Request $request)
    {
        $this->view->render("users");
    }

    public function testing(Request $request)
    {
        $this->view->render("users");
    }
}