<?php

declare(strict_types=1);

/**
 * ===============================================
 * ==================           ==================
 * ****** AdminController
 * ==================           ==================
 * ===============================================
 */

namespace Bolt\controllers;

use Bolt\Bolt\Controller;

class AdminController extends Controller
{
    public function welcome()
    {
        $data = [
            'title' => 'Bolt Framework',
            'header' => 'Hello, User! Welcome to Bolt Framework',
            'text' => 'You are most welcome to our world.',
            'items' => ['Item 1', 'Item 2', 'Item 3'],
        ];

        $this->view->render("admin", $data);
    }

    public function onConstruct(): void
    {   
    }
}