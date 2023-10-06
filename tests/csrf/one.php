<?php

<?php

class Csrf_u
{
    protected $token;

    public function __construct()
    {
        // Generate a CSRF token if it doesn't exist
        if (!isset($_SESSION['csrf_token'])) {
            $this->generateToken();
        } else {
            // Use the existing token
            $this->token = $_SESSION['csrf_token'];
        }
    }

    public function generateToken()
    {
        // Generate a random token
        $this->token = bin2hex(random_bytes(32));

        // Store the token in the session for later validation
        $_SESSION['csrf_token'] = $this->token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function validateToken($submittedToken)
    {
        // Compare the submitted token with the stored token
        return hash_equals($this->token, $submittedToken);
    }
}
