<?php

declare(strict_types=1);

/**
 * =====================================================
 * =====================        ========================
 * Bolt Authentication
 * =====================        ========================
 * =====================================================
 */

namespace Bolt\Bolt\Authentication;

use Bolt\Bolt\Bolt;
use Bolt\Bolt\Cookie;
use Bolt\Bolt\Database\Database;
use Bolt\Bolt\Database\DatabaseModel;
use Bolt\Bolt\Session;

class BoltAuthentication extends DatabaseModel
{
    protected $_current_user = false;
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

    public function login($email, $password)
    {
        // Check if the login attempts exceed the limit.
        if ($this->rateLimiter->exceedsLimit($email)) {
            // Handle rate limiting exceeded, e.g., display an error message.
            return false;
        }

        $user = $this->findByEmail($email);

        // Verify the password using a secure hashing algorithm like bcrypt
        if ($user && password_verify($password, $user->password)) {
            $this->_current_user = $user;

            dd($this->_current_user);

            // Reset the login attempts for this user
            $this->rateLimiter->resetAttempts($this->_current_user->email);

            // Set session variables for the authenticated user
            $this->session->set('user_id', $user['id']);
            $this->session->set('username', $user['username']);

            return true;
        } else {
            // Increase the login attempts count
            $this->rateLimiter->incrementAttempts($email);
        }

        return false;
    }

    private function fromCookie()
    {
        $user_cookie_name = Bolt::$bolt->config->get("user_token");

        if (!Cookie::has($user_cookie_name))
            return false;

        $hash = Cookie::get($user_cookie_name);
    }

    private function logout()
    {
        $this->session->remove("user");
        $this->_current_user = false;
    }

    public function getCurrentUser()
    {
        if (!isset($this->_current_user) && $this->session->has("user")) {
            $user = $this->session->get("user");
            $this->_current_user = $this->findOne([
                'uuid' => "uuid"
            ]);
        }

        if (!$this->_current_user)
            $this->fromCookie();

        if ($this->_current_user && $this->_current_user->blocked) {
            $this->logout();
        }

        return $this->_current_user;
    }
}
