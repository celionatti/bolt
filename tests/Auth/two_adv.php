<?php

class Authenticator
{
    private $db; // Database connection
    private $user;
    private $session;
    private $loginAttempts = []; // Store login attempts in-memory

    public function __construct($db, $session)
    {
        $this->db = $db;
        $this->user = null;
        $this->session = $session;
    }

    // public function login($username, $password)
    // {
    //     // Replace this query with your actual database query to retrieve user data
    //     $query = "SELECT * FROM users WHERE username = :username";
    //     $stmt = $this->db->prepare($query);
    //     $stmt->bindParam(':username', $username);
    //     $stmt->execute();
    //     $user = $stmt->fetch();

    //     // Verify the password using a secure hashing algorithm like bcrypt
    //     if ($user && password_verify($password, $user['password'])) {
    //         $this->user = $user;

    //         // Set session variables for the authenticated user
    //         $this->session->set('user_id', $user['id']);
    //         $this->session->set('username', $user['username']);

    //         return true;
    //     }

    //     return false;
    // }

    public function login($username, $password)
    {
        // Check if there have been too many login attempts in a short period
        if ($this->isLoginRateLimited($username)) {
            // Handle rate limit exceeded
            return false;
        }

        // Continue with the login logic
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        // Verify the password using a secure hashing algorithm like bcrypt
        if ($user && password_verify($password, $user['password'])) {
            $this->user = $user;

            // Reset the login attempts for this user
            $this->resetLoginAttempts($username);

            // Set session variables for the authenticated user
            $this->session->set('user_id', $user['id']);
            $this->session->set('username', $user['username']);

            return true;
        } else {
            // Increase the login attempts count
            $this->increaseLoginAttempts($username);
        }

        return false;
    }

    private function isLoginRateLimited($username)
    {
        $maxAttempts = 5; // Set your desired maximum login attempts
        $lockoutTime = 300; // Set the lockout time in seconds

        if (isset($this->loginAttempts[$username])) {
            $attempts = $this->loginAttempts[$username]['attempts'];
            $lastAttemptTime = $this->loginAttempts[$username]['time'];

            if ($attempts >= $maxAttempts && time() - $lastAttemptTime < $lockoutTime) {
                return true;
            }
        }

        return false;
    }

    private function increaseLoginAttempts($username)
    {
        if (isset($this->loginAttempts[$username])) {
            $this->loginAttempts[$username]['attempts']++;
            $this->loginAttempts[$username]['time'] = time();
        } else {
            $this->loginAttempts[$username] = [
                'attempts' => 1,
                'time' => time(),
            ];
        }
    }

    private function resetLoginAttempts($username)
    {
        if (isset($this->loginAttempts[$username])) {
            unset($this->loginAttempts[$username]);
        }
    }
    public function logout()
    {
        // Clear user-related session data
        $this->session->unset('user_id');
        $this->session->unset('username');

        $this->user = null;
    }

    public function isLoggedIn()
    {
        // Check if the user is authenticated based on the session
        return $this->user !== null || $this->session->get('user_id') !== null;
    }

    public function getUser()
    {
        if (!$this->user) {
            // Retrieve the user data from the database based on the session
            $user_id = $this->session->get('user_id');
            if ($user_id) {
                // Replace this query with your actual database query to retrieve user data
                $query = "SELECT * FROM users WHERE id = :user_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $this->user = $stmt->fetch();
            }
        }

        return $this->user;
    }

    public function hasPermission($permission)
    {
        // Check if the authenticated user has a specific permission
        $user = $this->getUser();
        if ($user && $user['role'] === 'admin') {
            // Admin users have all permissions
            return true;
        } elseif ($user && in_array($permission, $user['permissions'])) {
            return true;
        }

        return false;
    }
}
