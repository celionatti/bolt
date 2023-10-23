<?php

declare(strict_types=1);

namespace Bolt\Bolt\Authentication;

use Bolt\Bolt\Cookie;
use Bolt\Bolt\Session;
use Bolt\models\UserSessions;
use Bolt\Bolt\Helpers\FlashMessages\FlashMessage;
use Bolt\Bolt\Database\DatabaseModel;
use PDO;

class Refactor extends DatabaseModel
{
    private ?object $_currentUser = null;
    private Session $session;
    private RateLimiter $rateLimiter;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->session = new Session();
        $this->rateLimiter = new RateLimiter($db);
    }

    public static function tableName(): string
    {
        return "users";
    }

    public function login($email, $password, $rememberMe = false)
    {
        $isAccountBlocked = $this->isAccountBlocked($email);

        if ($isAccountBlocked) {
            FlashMessage::setMessage("Account is blocked. Please contact support.", FlashMessage::WARNING, ['role' => 'alert', 'style' => 'z-index: 9999;']);
        }

        if ($this->authenticate($email, $password)) {
            $this->resetLoginAttempts($email);
            if ($rememberMe) {
                $this->generateAndStoreRememberMeToken($this->_currentUser->user_id);
            }
            $this->setAuthenticatedUser($this->_currentUser->user_id);
        } else {
            $this->incrementLoginAttempts($email);
            FlashMessage::setMessage("Login failed. Please try again.", FlashMessage::WARNING, ['role' => 'alert', 'style' => 'z-index: 9999;']);
        }

        return false;
    }

    private function isAccountBlocked($email)
    {
        $isBlocked = $this->checkUserBlockedStatus($email);
        return $isBlocked;
    }

    private function checkUserBlockedStatus($email)
    {
        $result = $this->getUserBlockedStatus($email);

        if ($result && isset($result->is_blocked) && $result->is_blocked == 1) {
            return true;
        }

        return false;
    }

    private function getUserBlockedStatus($email)
    {
        return $this->findOne(['email' => $email], ['is_blocked']);
    }

    private function authenticate($email, $password)
    {
        $user = $this->getUserByCredentials($email, $password);

        if ($user && password_verify($password, $user->password) && $user->is_blocked == 0) {
            $this->_currentUser = $user;
            return true;
        }

        return false;
    }

    private function getUserByCredentials($email, $password)
    {
        return $this->findOne(['email' => $email], ['user_id', 'password', 'is_blocked']);
    }

    private function resetLoginAttempts($email)
    {
        $this->rateLimiter->resetAttempts($email);
    }

    private function incrementLoginAttempts($email)
    {
        $this->rateLimiter->incrementAttempts($email);
    }

    private function setAuthenticatedUser($userId)
    {
        $this->session->set("authenticated_user", $userId);
    }

    private function generateAndStoreRememberMeToken($userId)
    {
        $token = $this->generateRememberMeToken($userId);
        $this->storeRememberMeToken($userId, $token);
    }

    private function generateRememberMeToken($userId)
    {
        $selector = bin2hex(random_bytes(12));
        $rawToken = random_bytes(32);
        $token = $selector . $rawToken;
        return base64_encode($token);
    }

    private function storeRememberMeToken($userId, $token)
    {
        // Store the token in the user_sessions table with an expiration timestamp.
        $expiration = time() + 30 * 24 * 60 * 60;
        UserSessions::create(['user_id' => $userId, 'token_hash' => $token, 'expiration' => $expiration]);
        Cookie::set("remember_me_auth_token", $token, $expiration);
    }

    public function getCurrentUser()
    {
        if (!$this->_currentUser && $this->session->has("authenticated_user")) {
            $userId = $this->session->get("authenticated_user");
            $this->_currentUser = $this->findOne(['user_id' => $userId]);
        }

        if (!$this->_currentUser) {
            $this->fromCookie();
        }

        if ($this->_currentUser && $this->_currentUser->is_blocked) {
            $this->logout();
        }

        return $this->_currentUser;
    }

    private function fromCookie()
    {
        if (Cookie::has("remember_me_auth_token")) {
            $hash = Cookie::get("remember_me_auth_token");
            $session = UserSessions::findByHash($hash);
            if ($session) {
                $this->setAuthenticatedUser($session->user_id);
            }
        }
    }

    private function logout()
    {
        $this->clearUserSessions($this->_currentUser->user_id);
        $this->session->remove("authenticated_user");
        $this->_currentUser = null;
        Cookie::delete("remember_me_auth_token");
    }

    private function clearUserSessions($userId)
    {
        UserSessions::delete(['user_id' => $userId]);
    }
}
