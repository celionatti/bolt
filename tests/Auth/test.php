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

class BoltAuthentication_test extends DatabaseModel
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

    private function isAccountBlocked($email)
    {
        // Implement your actual account blocking logic here.
        // Check if the 'blocked' field is set to indicate a blocked account in your user database.

        $isBlocked = $this->checkUserBlockedStatus($email);

        return $isBlocked;
    }

    private function checkUserBlockedStatus($email)
    {
        // Implement your actual database query to check the 'blocked' status of the account.

        $result = $this->executeRawQuery("SELECT is_blocked FROM users WHERE email = :email", [':email' => $email]);

        // Check if the 'blocked' field exists and is set to 1 (blocked).
        if ($result[0] && isset($result[0]->is_blocked) && $result[0]->is_blocked == 1) {
            return true;
        }

        return false;
    }

    private function authenticate($email, $password)
    {
        // Replace with your actual authentication logic.
        // For demonstration, we assume successful authentication if the email and password match.

        // You should replace the following line with your authentication logic.
        // Check if the email and password match in your user database.
        $isAuthenticated = $this->checkUserCredentials($email, $password);

        return $isAuthenticated;
    }

    private function checkUserCredentials($email, $password)
    {
        // Implement your actual user authentication logic here.
        $user = $this->findByEmail($email);
        dd($user);
        // if ($email === 'user@example.com' && $password === 'password123') {
        //     return true;
        // }

        return false;
    }

    public function login($email, $password)
    {
        // Check if the account is blocked. You can implement this logic in your user table.
        $isAccountBlocked = $this->isAccountBlocked($email);

        if ($isAccountBlocked) {
            dd("Account is blocked. Please contact support.");
        }

        // If the account is not blocked, proceed with login attempt.
        if ($this->authenticate($email, $password)) {
            // Successful login. Reset the login attempts.
            $this->rateLimiter->resetAttempts($email);
            return "Login successful.";
        } else {
            // Failed login attempt. Increment login attempts.
            $this->rateLimiter->incrementAttempts($email);
            return "Login failed. Please try again.";
        }

        // $user = $this->findByEmail($email);

        // Verify the password using a secure hashing algorithm like bcrypt
        // if ($user && password_verify($password, $user->password)) {
        //     $this->_current_user = $user;

        //     dd($this->_current_user);

        //     // Reset the login attempts for this user
        //     // $this->rateLimiter->resetAttempts($this->_current_user->email);

        //     // Set session variables for the authenticated user
        //     // $this->session->set('user_id', $user['id']);
        //     // $this->session->set('username', $user['username']);

        //     // return true;
        // } else {
        //     // Increase the login attempts count
        //     $this->rateLimiter->incrementAttempts($email);
        // }

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
