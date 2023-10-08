<?php

declare(strict_types=1);

namespace Bolt\controllers;

use Bolt\Bolt\Controller;

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
}