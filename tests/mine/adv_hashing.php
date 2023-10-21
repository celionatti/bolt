<?php
function customHash($password, $salt = null, $iterations = 10000) {
    if ($salt === null) {
        $salt = random_bytes(16); // Generate a random salt
    }

    $hash = $password;
    for ($i = 0; $i < $iterations; $i++) {
        $hash = hash('sha256', $hash . $salt);
    }

    return [$hash, $salt];
}

function verifyCustomHash($password, $storedHash, $storedSalt, $iterations = 10000) {
    list($newHash, $salt) = customHash($password, $storedSalt, $iterations);
    
    if (hash_equals($newHash, $storedHash)) {
        return true; // Password is correct
    } else {
        return false; // Password is incorrect
    }
}

// Example of using these functions to hash and verify a password
$password = "user_password";
list($hash, $salt) = customHash($password);
echo "Hash: $hash\nSalt: $salt\n";

// To verify a password
$userEnteredPassword = "user_password";
if (verifyCustomHash($userEnteredPassword, $hash, $salt)) {
    echo "Password is correct.\n";
} else {
    echo "Password is incorrect.\n";
}
?>
