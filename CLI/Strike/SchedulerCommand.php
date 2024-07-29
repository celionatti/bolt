<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Scheduler Command =========
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CliActions;
use celionatti\Bolt\CLI\CommandInterface;
use celionatti\Bolt\BoltException\BoltException;

class SchedulerCommand extends CliActions implements CommandInterface
{
    public $basePath;

    private const TASK = 'task';
    private const JOB = 'job';
    private const EVENT = 'event';

    private const ACTIONS = [
        self::TASK => 'Create a new scheduled task',
        self::JOB => 'Create a new job',
        self::EVENT => 'Create a new event'
    ];

    public function __construct()
    {
        $this->configure();
    }

    public function execute(array $args)
    {
        // Check if no action is provided
        if (empty($args) || empty($args["args"])) {
            $this->listAvailableActions();
            return;
        }

        $action = $args["args"][0] ?? null;

        if ($action === null) {
            $this->listAvailableActions();
            return;
        }

        $this->callAction($action);
    }

    private function callAction($action)
    {
        // Check for the action type.
        switch ($action) {
            case self::TASK:
                $this->createTask();
                break;
            case self::JOB:
                $this->createJob();
                break;
            case self::EVENT:
                $this->createEvent();
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function createTask()
    {
        $taskName = $this->prompt("Enter the task name");

        if (empty($taskName)) {
            $this->message("Task name cannot be empty.", true, true, "error");
            return;
        }

        $taskDir = $this->basePath . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "tasks";

        if (!is_dir($taskDir)) {
            if (!mkdir($taskDir, 0755, true)) {
                $this->message("Unable to create the task directory.", true, true, "error");
                return;
            }
        }

        $taskFile = $taskDir . DIRECTORY_SEPARATOR . ucfirst($taskName) . 'Task.php';

        if (file_exists($taskFile)) {
            $this->message("Task file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/task/task-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Task sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($taskName) . 'Task';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($taskFile, $content) === false) {
            $this->message("Unable to create the task file.", true, true, "error");
            return;
        }

        $this->message("Task: [{$taskFile}] created successfully", false, true, "created");
    }

    private function createJob()
    {
        $jobName = $this->prompt("Enter the job name");

        if (empty($jobName)) {
            $this->message("Job name cannot be empty.", true, true, "error");
            return;
        }

        $jobDir = $this->basePath . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "jobs";

        if (!is_dir($jobDir)) {
            if (!mkdir($jobDir, 0755, true)) {
                $this->message("Unable to create the job directory.", true, true, "error");
                return;
            }
        }

        $jobFile = $jobDir . DIRECTORY_SEPARATOR . ucfirst($jobName) . 'Job.php';

        if (file_exists($jobFile)) {
            $this->message("Job file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/job/job-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Job sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($jobName) . 'Job';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($jobFile, $content) === false) {
            $this->message("Unable to create the job file.", true, true, "error");
            return;
        }

        $this->message("Job: [{$jobFile}] created successfully", false, true, "created");
    }

    private function createEvent()
    {
        $eventName = $this->prompt("Enter the event name");

        if (empty($eventName)) {
            $this->message("Event name cannot be empty.", true, true, "error");
            return;
        }

        $eventDir = $this->basePath . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "events";

        if (!is_dir($eventDir)) {
            if (!mkdir($eventDir, 0755, true)) {
                $this->message("Unable to create the event directory.", true, true, "error");
                return;
            }
        }

        $eventFile = $eventDir . DIRECTORY_SEPARATOR . ucfirst($eventName) . 'Event.php';

        if (file_exists($eventFile)) {
            $this->message("Event file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/event/event-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Event sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($eventName) . 'Event';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($eventFile, $content) === false) {
            $this->message("Unable to create the event file.", true, true, "error");
            return;
        }

        $this->message("Event: [{$eventFile}] created successfully", false, true, "created");
    }

    private function listAvailableActions()
    {
        $this->message("Available Scheduler Commands:", false, false, 'info');
        foreach (self::ACTIONS as $action => $description) {
            $this->output("  \033[0;37m{$action}\033[0m: \033[0;36m{$description}\033[0m", 1);
        }
    }
}
