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
use Bolt\Bolt\Bolt;
use Bolt\models\Users;
use Bolt\Bolt\Controller;
use Bolt\Bolt\Http\Request;
use Bolt\Bolt\Helpers\FlashMessages\FlashMessage;

class AuthController extends Controller
{
    public function onConstruct(): void
    {
        if ($this->currentUser = BoltAuthentication::currentUser()) {
            redirect("/");
        }
    }

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
            $user->setIsInsertionScenario(true); // Set insertion scenario flag
            $user->passwordsMatchValidation($data['password'], $data['confirm_password']);
            if ($user->validate($data)) {
                // other method before saving.
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
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

    public function login_view(Request $request)
    {
        $view = [
            'errors' => Bolt::$bolt->session->getFormMessage(),
            'user' => $this->retrieveUserSessionData(),
        ];

        $this->view->render("auth/login", $view);
    }

    public function login(Request $request)
    {
        $user = new Users();

        if ($request->isPost()) {
            $data = $request->getBody();
            $user->setIsInsertionScenario(false); // Set insertion scenario flag
            if ($user->validate($data)) {
                $auth = new BoltAuthentication();
                $auth->login($data['email'], $data['password']);
            } else {
                $this->storeUserSessionData($data);
            }
        }
        Bolt::$bolt->session->setFormMessage($user->getErrors());
        redirect("/login");
    }

    protected function retrieveUserSessionData()
    {
        return Bolt::$bolt->session->get('user_data', []);
    }

    protected function storeUserSessionData(array $data)
    {
        Bolt::$bolt->session->set('user_data', $data);
    }
}
