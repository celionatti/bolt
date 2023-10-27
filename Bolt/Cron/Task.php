<?php

declare(strict_types=1);

/**
 * ============================================
 * ================         ===================
 * Task
 * ===============          ===================
 * ============================================
 */

namespace Bolt\Bolt\Cron;

class Task
{
    protected $command;
    protected $interval;
    protected $lastExecuted;

    public function __construct($command, $interval)
    {
        $this->command = $command;
        $this->interval = $interval;
        $this->lastExecuted = null;
    }

    public function isDue($currentTime)
    {
        return $this->lastExecuted === null || $currentTime - $this->lastExecuted >= $this->interval;
    }

    public function execute()
    {
        // Execute the specified command here.
        // You can use shell_exec, exec, or any other method based on your needs.
        // Example: shell_exec($this->command);

        // Ensure that the command is safely escaped to prevent security vulnerabilities.
        $escapedCommand = escapeshellcmd($this->command);

        // Execute the command and capture the output if needed.
        // Note: The use of shell_exec is a simplified example; consider using more advanced methods for command execution.
        $output = shell_exec($escapedCommand);

        // You can log or handle the output as needed.
        if ($output !== null) {
            // Handle or log the output here.
            // Example: file_put_contents('output.log', $output, FILE_APPEND);
        }

        // Update the last executed time
        $this->lastExecuted = time();
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getNextExecutionTime()
    {
        return $this->lastExecuted + $this->interval;
    }
}
