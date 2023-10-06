<?php

class CLI
{
    protected $commands = [];

    public function registerCommand($name, $description, $callback)
    {
        $this->commands[$name] = [
            'description' => $description,
            'callback' => $callback,
        ];
    }

    public function parseArguments()
    {
        global $argv;

        // Remove the script name from the arguments
        array_shift($argv);

        if (count($argv) < 1) {
            $this->printHelp();
            exit(1);
        }

        $commandName = array_shift($argv);
        if (!isset($this->commands[$commandName])) {
            $this->printHelp();
            exit(1);
        }

        $command = $this->commands[$commandName];
        $args = [];

        foreach ($argv as $arg) {
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $args[$key] = $value;
            }
        }

        return [$command, $args];
    }

    public function run()
    {
        list($command, $args) = $this->parseArguments();
        $callback = $command['callback'];

        if (is_callable($callback)) {
            call_user_func($callback, $args);
        } else {
            echo "Invalid callback for the command.\n";
            exit(1);
        }
    }

    protected function printHelp()
    {
        echo "Available commands:\n";
        foreach ($this->commands as $name => $command) {
            echo "$name: {$command['description']}\n";
        }
    }
}


/**
 * Usage
 */

$cli = new CLI();

// Register commands
$cli->registerCommand('hello', 'Prints a hello message', function ($args) {
    $name = isset($args['name']) ? $args['name'] : 'World';
    echo "Hello, $name!\n";
});

$cli->registerCommand('add', 'Adds two numbers', function ($args) {
    $num1 = isset($args['num1']) ? (int)$args['num1'] : 0;
    $num2 = isset($args['num2']) ? (int)$args['num2'] : 0;
    echo "Result: " . ($num1 + $num2) . "\n";
});

// Run the CLI application
$cli->run();


/**
 * More
 */

$cli = new CLI();

// Register commands
$cli->registerCommand('hello', 'Prints a hello message', function ($args) {
    $name = isset($args['name']) ? $args['name'] : 'World';
    echo "Hello, $name!\n";
});

$cli->registerCommand('add', 'Adds two numbers', function ($args) {
    $num1 = isset($args['num1']) ? (int)$args['num1'] : 0;
    $num2 = isset($args['num2']) ? (int)$args['num2'] : 0;
    echo "Result: " . ($num1 + $num2) . "\n";
});

$cli->registerCommand('subtract', 'Subtracts two numbers', function ($args) {
    $num1 = isset($args['num1']) ? (int)$args['num1'] : 0;
    $num2 = isset($args['num2']) ? (int)$args['num2'] : 0;
    echo "Result: " . ($num1 - $num2) . "\n";
});

$cli->registerCommand('multiply', 'Multiplies two numbers', function ($args) {
    $num1 = isset($args['num1']) ? (int)$args['num1'] : 0;
    $num2 = isset($args['num2']) ? (int)$args['num2'] : 0;
    echo "Result: " . ($num1 * $num2) . "\n";
});

$cli->registerCommand('divide', 'Divides two numbers', function ($args) {
    $num1 = isset($args['num1']) ? (int)$args['num1'] : 0;
    $num2 = isset($args['num2']) ? (int)$args['num2'] : 0;

    if ($num2 === 0) {
        echo "Cannot divide by zero.\n";
    } else {
        echo "Result: " . ($num1 / $num2) . "\n";
    }
});

// Run the CLI application
$cli->run();
