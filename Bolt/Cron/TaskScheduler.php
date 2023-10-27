<?php

declare(strict_types=1);

/**
 * ============================================
 * ================         ===================
 * TaskScheduler
 * ===============          ===================
 * ============================================
 */

namespace Bolt\Bolt\Cron;

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


// // Example usage:
// $scheduler = new TaskScheduler();

// // Schedule tasks
// $task1 = new Task('php script1.php', 60);
// $task2 = new Task('php script2.php', 120);
// $scheduler->schedule($task1);
// $scheduler->schedule($task2);

// // Run due tasks (you would typically run this periodically)
// $scheduler->runDueTasks();

// // Get the next due task
// $nextTask = $scheduler->getNextDueTask();
// if ($nextTask) {
//     echo "Next task to run: " . $nextTask->getCommand() . PHP_EOL;
// }