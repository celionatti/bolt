<?php

declare(strict_types=1);

/**
 * =====================================================
 * =====================        ========================
 * Bolt Authentication
 * =====================        ========================
 * =====================================================
 */

namespace celionatti\Bolt\Authentication;

use celionatti\Bolt\Cookie;
use celionatti\Bolt\Session;
use PhpStrike\models\UserSessions;
use celionatti\Bolt\Database\Database;
use celionatti\Bolt\Database\DatabaseModel;
use celionatti\Bolt\Helpers\FlashMessages\FlashMessage;

class BoltAuthentication extends DatabaseModel
{
    private ?object $_currentUser = null;
    private Session $session;
    private RateLimiter $rateLimiter; // Inject the RateLimiter here

    public function __construct()
    {
        parent::__construct();
        $database = new Database();
        $this->session = new Session();
        $this->rateLimiter = new RateLimiter($database->getConnection());
    }

    public static function tableName(): string
    {
        return "users";
    }

    public function login($email, $password, $rememberMe = false, $redirect = "/")
    {
        $isAccountBlocked = $this->isAccountBlocked($email);
        $isValidCredentials = $this->authenticate($email, $password);
        $isValidEmail = $this->getUserValidEmail($email);

        session_regenerate_id();

        if ($isAccountBlocked) {
            // Display a message indicating that the account is blocked.
            FlashMessage::setMessage("Account is blocked. Please contact support.", FlashMessage::WARNING, ['role' => 'alert', 'style' => 'z-index: 9999;']);
        } elseif ($isValidCredentials) {
            $this->resetLoginAttempts($email);
            if ($rememberMe) {
                $this->generateAndStoreRememberMeToken($this->_currentUser->user_id);
            }
            $this->setAuthenticatedUser($this->_currentUser->user_id);
            redirect($redirect);
        } else {
            if ($isValidEmail) {
                $this->incrementLoginAttempts($email);
            }
            // Invalid password. Update login attempts.
            FlashMessage::setMessage("Invalid email or password. Please try again.", FlashMessage::DANGER, ['role' => 'alert', 'style' => 'z-index: 9999;']);
        }

        return $isValidCredentials;
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

    private function getUserValidEmail($email)
    {
        return $this->findOne(['email' => $email], ['email']);
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
        $this->session->set("auth_user", $userId);
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
        $userSessions = new UserSessions();
        // Store the token in the user_sessions table with an expiration timestamp.
        $expiration = time() + 30 * 24 * 60 * 60;
        $userSessions->fillable([
            'user_id',
            'token_hash',
            'expiration'
        ]);
        $userSessions->createrecord([
            'user_id' => $userId, 
            'token_hash' => $token, 
            'expiration' => $expiration
        ]);
        Cookie::set("natti_at", $token, $expiration);
    }

    public function getCurrentUser()
    {
        if (!$this->_currentUser && $this->session->has("auth_user")) {
            $userId = $this->session->get("auth_user");
            $this->_currentUser = $this->findOne(['user_id' => $userId]);
        }

        if (!$this->_currentUser) {
            $this->fromCookie();
        }

        if ($this->_currentUser && $this->_currentUser->is_blocked) {
            $this->logout();
        }

        return $this->_currentUser ?? null;
    }

    public static function currentUser()
    {
        $instance = new self();
        return $instance->getCurrentUser();
    }

    private function fromCookie()
    {
        $userSessions = new UserSessions();
        if (Cookie::has("natti_at")) {
            $hash = Cookie::get("natti_at");
            $session = $userSessions->findByHash($hash);
            if ($session) {
                $this->setAuthenticatedUser($session->user_id);
            }
        }
    }

    public function logout()
    {
        $this->clearUserSessions($this->getCurrentUser()->user_id);
        $this->session->remove("auth_user");
        $this->_currentUser = null;
        Cookie::delete("natti_at");
        session_regenerate_id();
    }

    private function clearUserSessions($userId)
    {
        $userSessions = new UserSessions();
        $userSessions->deleteBy(['user_id' => $userId]);
    }
}
