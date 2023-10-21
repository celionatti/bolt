<?php

function customHash($password) {
    // Add a unique salt to the password
    $salt = "your_salt_here"; // Replace with a random salt
    $password = $salt . $password;

    // Apply a hashing algorithm (e.g., sha256)
    $hashedPassword = hash('sha256', $password);

    return $hashedPassword;
}


function verifyCustomHash($password, $storedHash) {
    // Hash the provided password with the same salt
    $hashedPassword = customHash($password);

    // Compare the stored hash with the newly generated hash
    if ($hashedPassword === $storedHash) {
        return true; // Password is correct
    } else {
        return false; // Password is incorrect
    }
}
