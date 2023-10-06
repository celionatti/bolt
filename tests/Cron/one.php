<?php

class TaskScheduler
{
    protected $tasks = [];

    public function schedule($command, $interval)
    {
        $this->tasks[] = [
            'command' => $command,
            'interval' => $interval,
            'lastExecuted' => null,
        ];
    }

    public function runDueTasks()
    {
        $now = time();

        foreach ($this->tasks as &$task) {
            if ($this->isDue($task, $now)) {
                $this->executeCommand($task['command']);
                $task['lastExecuted'] = $now;
            }
        }
    }

    protected function isDue($task, $now)
    {
        return $task['lastExecuted'] === null || $now - $task['lastExecuted'] >= $task['interval'];
    }

    protected function executeCommand($command)
    {
        // Execute the specified command here.
        // You can use shell_exec, exec, or any other method based on your needs.
        // Example: shell_exec($command);
        // Remember to handle any error handling and command execution properly.
    }
}
