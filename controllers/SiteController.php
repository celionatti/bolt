<?php

declare(strict_types=1);

/**
 * ===================================================
 * =================            ======================
 * SiteController
 * =================            ======================
 * ===================================================
 */ 

namespace Bolt\controllers;

use Bolt\Bolt\Controller;
use Bolt\Bolt\Http\Request;

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
}