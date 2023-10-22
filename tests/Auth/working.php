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

use Bolt\Bolt\Cookie;
use Bolt\Bolt\Session;
use Bolt\models\UserSessions;
use Bolt\Bolt\Database\Database;
use Bolt\Bolt\Database\DatabaseModel;
use Bolt\Bolt\Helpers\FlashMessages\FlashMessage;

class working extends DatabaseModel
{
    private ?object $_current_user = null;
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
        $isBlocked = $this->checkUserBlockedStatus($email);

        return $isBlocked;
    }

    private function checkUserBlockedStatus($email)
    {
        $result = $this->executeRawQuery("SELECT is_blocked FROM users WHERE email = :email", [':email' => $email]);

        if ($result[0] && isset($result[0]->is_blocked) && $result[0]->is_blocked == 1) {
            return true;
        }

        return false;
    }

    private function authenticate($email, $password)
    {
        // Replace with your actual authentication logic.
        $isAuthenticated = $this->checkUserCredentials($email, $password);

        return $isAuthenticated;
    }

    private function checkUserCredentials($email, $password)
    {
        // Implement your actual user authentication logic here.
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user->password) && $user->is_blocked == 0) {
            $this->_current_user = $user;
            return true;
        }

        return false;
    }

    public function login($email, $password, $rememberMe = false)
    {
        // Check if the account is blocked. You can implement this logic in your user table.
        $isAccountBlocked = $this->isAccountBlocked($email);

        if ($isAccountBlocked) {
            FlashMessage::setMessage("Account is blocked. Please contact support.", FlashMessage::WARNING, ['role' => 'alert', 'style' => 'z-index: 9999;']);
        }

        // If the account is not blocked, proceed with login attempt.
        if ($this->authenticate($email, $password)) {
            // Successful login. Reset the login attempts.
            $this->rateLimiter->resetAttempts($email);
            if ($rememberMe) {
                $token_time = time() + 30 * 24 * 60 * 60;
                // Generate a long-lived authentication token
                $token = $this->generateRememberMeToken($this->_current_user->user_id, $token_time);
                // Store the token in a cookie
                Cookie::set("remember_me_auth_token", $token, $token_time);
            }
            $this->session->set("authenticated_user", $this->_current_user->user_id);
        } else {
            // Failed login attempt. Increment login attempts.
            $this->rateLimiter->incrementAttempts($email);
            FlashMessage::setMessage("Login failed. Please try again.", FlashMessage::WARNING, ['role' => 'alert', 'style' => 'z-index: 9999;']);
        }

        return false;
    }

    private function fromCookie()
    {
        if (!Cookie::has("remember_me_auth_token"))
            return false;
        $hash = Cookie::get("remember_me_auth_token");
        $session = UserSessions::findByHash($hash);
        if (!$session)
            return false;
        $user = $this->findOne([
            'user_id' => $session->user_id,
        ]);
        if ($user) {
            $this->session->set("authenticated_user", $user->user_id);
        }
    }

    private function logout()
    {
        $query = "DELETE FROM user_sessions WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $this->_current_user->user_id);
        $stmt->execute();
        $this->session->remove("authenticated_user");
        $this->_current_user = false;
        Cookie::delete("remember_me_auth_token");
    }

    public function getCurrentUser()
    {
        if (!isset($this->_current_user) && $this->session->has("authenticated_user")) {
            $user = $this->session->get("authenticated_user");
            $this->_current_user = $this->findOne([
                'user_id' => $user
            ]);
        }

        if (!$this->_current_user)
            $this->fromCookie();

        if ($this->_current_user && $this->_current_user->is_blocked) {
            $this->logout();
        }

        return $this->_current_user;
    }

    private function generateRememberMeToken($userId, $expiration)
    {
        $selector = bin2hex(random_bytes(12)); // 12 bytes for the selector
        $rawToken = random_bytes(32); // 32 bytes for the random token

        // Create a combined token (selector + raw token)
        $token = $selector . $rawToken;

        // Encode the token with base64 encoding to ensure URL-safe characters
        $tokenHash = base64_encode($token);

        // Store the token hash in the database along with the user ID and expiration time
        $this->storeRememberMeToken($userId, $tokenHash, $expiration);

        return $tokenHash;
    }

    private function storeRememberMeToken($userId, $tokenHash, $expiration)
    {
        // Assuming you have a 'remember_me_tokens' table in your database
        $query = "INSERT INTO user_sessions (user_id, token_hash, expiration) VALUES (:user_id, :token_hash, :expiration)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':token_hash', $tokenHash);
        $stmt->bindParam(':expiration', $expiration);
        $stmt->execute();
    }
}
