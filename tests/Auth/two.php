<?php

class Authenticator
{
    private $db; // Database connection
    private $user;

    public function __construct($db)
    {
        $this->db = $db;
        $this->user = null;
    }

    public function login($username, $password)
    {
        // Hash and sanitize the password (use a secure hashing algorithm like bcrypt)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Replace this query with your actual database query to check if the user exists
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        // Verify the password
        if ($user && password_verify($password, $user['password'])) {
            $this->user = $user;
            return true;
        }

        return false;
    }

    public function logout()
    {
        $this->user = null;
    }

    public function isLoggedIn()
    {
        return $this->user !== null;
    }

    public function getUser()
    {
        return $this->user;
    }
}
?>
