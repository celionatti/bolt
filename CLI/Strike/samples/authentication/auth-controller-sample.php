<?php

declare(strict_types=1);

/**
 * ===================================================
 * =================            ======================
 * AuthController
 * =================            ======================
 * ===================================================
 */

namespace PhpStrike\controllers;

use celionatti\Bolt\Authentication\BoltAuthentication;
use celionatti\Bolt\Bolt;
use Bolt\models\Users;
use celionatti\Bolt\Controller;
use celionatti\Bolt\Helpers\Csrf;
use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Helpers\FlashMessages\FlashMessage;

class AuthController extends Controller
{
    public function onConstruct(): void
    {
        if ($this->currentUser = BoltAuthentication::currentUser()) {
            redirect("/");
        }
    }

    public function signup_view(Request $request)
    {
        $view = [
            'errors' => Bolt::$bolt->session->getFormMessage(),
            'user' => $this->retrieveUserSessionData(),
            'genderOpts' => [
                'male' => 'Male',
                'female' => 'Female',
                'others' => 'Others'
            ]
        ];

        // Remove the user data from the session after it has been retrieved
        Bolt::$bolt->session->unsetArray(['user_data']);

        $this->view->render("auth/signup", $view);
    }

    public function signup(Request $request)
    {
        $user = new Users();
        $csrf = new Csrf();

        if ($request->isPost()) {
            $user->fillable([
                'user_id',
                'surname',
                'othername',
                'phone',
                'email',
                'gender',
                'role',
                'password'
            ]);
            $data = $request->getBody();
            if (!$csrf->validateToken($data["_csrf_token"])) {
                bolt_die("CSRF Token Expires");
                return;
            }
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
            } else {
                $this->storeUserSessionData($data);
            }
        }
        Bolt::$bolt->session->setFormMessage($user->getErrors());
        redirect("/signup");
    }

    public function login_view()
    {
        $view = [
            'errors' => Bolt::$bolt->session->getFormMessage(),
            'user' => $this->retrieveUserSessionData(),
        ];

        // Remove the user data from the session after it has been retrieved
        Bolt::$bolt->session->unsetArray(['user_data']);

        $this->view->render("auth/login", $view);
    }

    public function login(Request $request)
    {
        $user = new Users();
        $csrf = new Csrf();

        if ($request->isPost()) {
            $data = $request->getBody();
            if (!$csrf->validateToken($data["_csrf_token"])) {
                bolt_die("CSRF Token Expires");
                return;
            }

            $user->setIsInsertionScenario(false); // Set insertion scenario flag
            if ($user->validate($data)) {
                $auth = new BoltAuthentication();
                $auth->login($data['email'], $data['password'], $data['remember'] ?? false);
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

    public function logout(Request $request)
    {
        if ($request->isPost()) {
            $auth = new BoltAuthentication();

            if ($auth->logout()) {
                // Display a message indicating that the account is blocked.
                FlashMessage::setMessage("Logout Successfully.!", FlashMessage::SUCCESS, ['role' => 'alert', 'style' => 'z-index: 9999;']);
            }
        }
    }
}
