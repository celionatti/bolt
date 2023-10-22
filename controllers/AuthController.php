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

use Bolt\Bolt\Authentication\BoltAuthentication;
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
            $user->fillable([
                'user_id',
                'surname',
                'othername',
                'phone',
                'email',
                'role',
                'password'
            ]);
            $data = $request->getBody();
            $data['user_id'] = generateUuidV4();
            $user->passwordsMatchValidation($data['password'], $data['confirm_password']);
            if ($user->validate($data)) {
                // other method before saving.
                $data['password'] = hashPassword($data['password']);
                if ($user->insert($data)) {
                    FlashMessage::setMessage("User Created Successfully", FlashMessage::SUCCESS, ['role' => 'alert', 'style' => 'z-index: 9999;']);
                    redirect("/");
                }
            }
        }

        $view = [
            'errors' => $user->getErrors(),
            'user' => $user,
            'genderOpts' => [
                '' => '--- Please Select ---',
                'male' => 'Male',
                'female' => 'Female',
                'others' => 'Others'
            ]
        ];

        $this->view->render("auth/signup", $view);
    }

    public function login()
    {
        $auth = new BoltAuthentication();
        $auth->login("amisuusman@gmail.com", "Password23");
        $this->view->render("auth/login");
    }
}
