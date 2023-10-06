<?php

/**Register Command Classes: In your BoltCLI class, register each command along with its description and the corresponding command class. Here's an example: */
$cli = new BoltCLI();

$cli->registerCommand('model', 'Create a model and associated files', ModelCommand::class);
$cli->registerCommand('migration', 'Generate a migration file', MigrationCommand::class);
// Add more commands as needed

/**Parse Arguments and Dispatch: In your BoltCLI class, in the run method, after parsing the command name, you can dispatch the execution to the appropriate command class based on the registered command name:

 */

 public function run()
{
    list($commandName, $args, $options) = $this->parseArguments();

    if (!isset($this->commands[$commandName])) {
        $this->printHelp();
        exit(1);
    }

    $commandClass = $this->commands[$commandName]['commandClass'];
    $commandInstance = new $commandClass();

    $commandInstance->execute($args, $options);
}


/**Command Execution: Each command class (ModelCommand, MigrationCommand, etc.) should have an execute method that contains the logic for that specific command. For example: */

class ModelCommand
{
    public function execute($args, $options)
    {
        // Logic for creating models, generating migrations, etc.
        $modelName = $args[0] ?? null;
        // ... (implementation specific to the 'model' command)
    }
}


/**Run the CLI: To run the CLI, invoke your PHP script and pass the command name and any relevant arguments/options. For example: */

// php your_script.php model users --location=/path/to/custom/location --generate-migration



/**With this structure, when you run the script with a specific command name, such as model or migration, it will instantiate the corresponding command class and execute the execute method of that class with the provided arguments and options.

This approach allows you to organize your code more effectively and easily add new commands to your CLI application by creating additional command classes and registering them in the BoltCLI class. */
