<?php
// CLIACTIONS

// Basic message
$this->message("Operation completed successfully", 'success');

// Error message that exits
$this->message("Critical error occurred", 'error', true);

// Simple prompt
$name = $this->prompt("Enter your name", "Guest");

// Multiple choice
$option = $this->choice("Select action", [
    '1' => 'Create new user',
    '2' => 'Delete user',
    '3' => 'List users'
]);