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

use Bolt\Bolt\Bolt;
use Bolt\models\Users;
use Bolt\Bolt\Controller;
use Bolt\Bolt\Http\Request;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $user = new Users();

        if ($request->isPost()) {
            $data = $request->getBody();
            $user->allowedInsertParams = [
                'username',
                'name',
                'phone',
                'email',
                'acl',
                'password'
            ];
            if ($user->insert($data)) {
                Bolt::$bolt->session->setFlash("success", "User Created Successfully");
                redirect("/");
            }
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
