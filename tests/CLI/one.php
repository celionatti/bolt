<?php

class CLI
{
    protected $arguments;

    public function __construct()
    {
        $this->arguments = $_SERVER['argv'];
        array_shift($this->arguments); // Remove the script name from the arguments
    }

    public function getArgument($name)
    {
        $index = array_search('--' . $name, $this->arguments);
        if ($index !== false && isset($this->arguments[$index + 1])) {
            return $this->arguments[$index + 1];
        }
        return null;
    }

    public function hasArgument($name)
    {
        return in_array('--' . $name, $this->arguments);
    }

    public function executeCommand($command)
    {
        // Execute the specified command.
        // You can use shell_exec, exec, or any other method based on your needs.
        // Example: shell_exec($command);

        // Ensure that the command is safely escaped to prevent security vulnerabilities.
        $escapedCommand = escapeshellcmd($command);

        // Execute the command and capture the output if needed.
        // Note: The use of shell_exec is a simplified example; consider using more advanced methods for command execution.
        $output = shell_exec($escapedCommand);

        // You can log or handle the output as needed.
        if ($output !== null) {
            // Handle or log the output here.
            // Example: file_put_contents('output.log', $output, FILE_APPEND);
        }
    }
}


/**
 * Usage
 */

 $cli = new CLI();

if ($cli->hasArgument('help')) {
    echo "Usage: php script.php --option value\n";
    echo "--option: Description of the option\n";
    exit(0);
}

$optionValue = $cli->getArgument('option');
if ($optionValue !== null) {
    echo "Option value: " . $optionValue . "\n";
}

// Execute a command if needed
// $cli->executeCommand('your-command-here');
