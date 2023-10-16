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

use Bolt\models\Users;
use Bolt\Bolt\Controller;
use Bolt\Bolt\Http\Request;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $user = new Users();

        if($request->isPost()) {
            $user->loadData($request->getBody());
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