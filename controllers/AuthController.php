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
use Bolt\models\Users;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $user = new Users();
        dd($user);

        if($request->isPost()) {
            
        }

        $view = [
            'errors' => [],
            'uuid' => generateUuidV4()
        ];

        $this->view->render("auth/signup", $view);
    }

    public function login()
    {
        $this->view->render("auth/login");
    }
}