<?php

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

class TaskScheduler
{
    protected $tasks = [];

    public function schedule(Task $task)
    {
        $this->tasks[] = $task;
    }

    public function runDueTasks()
    {
        $currentTime = time();

        foreach ($this->tasks as $task) {
            if ($task->isDue($currentTime)) {
                $task->execute();
            }
        }
    }

    public function getNextDueTask()
    {
        $nextTask = null;
        $nextExecutionTime = PHP_INT_MAX;

        foreach ($this->tasks as $task) {
            $nextTime = $task->getNextExecutionTime();
            if ($nextTime < $nextExecutionTime) {
                $nextExecutionTime = $nextTime;
                $nextTask = $task;
            }
        }

        return $nextTask;
    }
}

// Example usage:
$scheduler = new TaskScheduler();

// Schedule tasks
$task1 = new Task('php script1.php', 60);
$task2 = new Task('php script2.php', 120);
$scheduler->schedule($task1);
$scheduler->schedule($task2);

// Run due tasks (you would typically run this periodically)
$scheduler->runDueTasks();

// Get the next due task
$nextTask = $scheduler->getNextDueTask();
if ($nextTask) {
    echo "Next task to run: " . $nextTask->getCommand() . PHP_EOL;
}
