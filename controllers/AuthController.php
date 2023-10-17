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
use Bolt\Bolt\Helpers\FlashMessages\FlashMessage;

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
                FlashMessage::setMessage("User Created Successfully");
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
