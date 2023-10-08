<?php

declare(strict_types=1);

/**
 * ===============================================
 * ==================           ==================
 * ****** ArticleController
 * ==================           ==================
 * ===============================================
 */

namespace Bolt\controllers;

use Bolt\Bolt\Controller;

class ArticleController extends Controller
{
    public function welcome()
    {
        $data = [
            'title' => 'Bolt Framework',
            'header' => 'Hello, User! Welcome to Bolt Framework',
            'text' => 'You are most welcome to our world.',
            'items' => ['Item 1', 'Item 2', 'Item 3'],
        ];

        $this->view->render("article", $data);
    }

    public function onConstruct(): void
    {   
    }
}