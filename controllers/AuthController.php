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
            $user->setAllowedInsertParams([
                'username',
                'name',
                'phone',
                'email',
                'acl',
                'password'
            ]);
            $data = $request->getBody();
            if ($user->validate($data)) {
                // if ($user->insert($data)) {
                //     FlashMessage::setMessage("User Created Successfully", FlashMessage::SUCCESS, ['role' => 'alert', 'style' => 'z-index: 9999;']);
                //     redirect("/");
                // }
            }
        }

        $view = [
            'errors' => $user->getErrors(),
            'user' => $user,
            'uuid' => generateUuidV4()
        ];

        $this->view->render("auth/signup", $view);
    }

    public function login()
    {
        $this->view->render("auth/login");
    }
}
