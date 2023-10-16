<?php

declare(strict_types=1);

/**
 * ===================================================
 * =================            ======================
 * AuthController
 * =================            ======================
 * ===================================================
 */ 

namespace Bolt\controllers;

use Bolt\Bolt\Controller;
use Bolt\Bolt\Http\Request;

class AuthController extends Controller
{
    public function signup()
    {
        $view = [
            'errors' => []
        ];

        $this->view->render("auth/signup", $view);
    }

    public function login()
    {
        $this->view->render("auth/login");
    }
}