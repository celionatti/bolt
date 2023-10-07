<?php

class ModelGeneratorCommand implements CommandInterface
{
    public function execute(array $args)
    {
        // Check if the required arguments are provided
        if (count($args) < 1) {
            echo "Usage: create-model <ModelName> [--table=tableName] [--fields=field1,field2,...]\n";
            exit(1);
        }

        $modelName = $args[0];
        $tableName = $this->getOption($args, '--table', $modelName);
        $fields = $this->getOption($args, '--fields', '');

        // Generate the model class content
        $modelContent = $this->generateModel($modelName, $tableName, $fields);

        // Write the content to a file (e.g., ModelName.php)
        $filename = $modelName . '.php';
        file_put_contents($filename, $modelContent);

        echo "Model '$modelName' created successfully.\n";
    }

    private function generateModel($modelName, $tableName, $fields)
    {
        // Generate and return the model class content
        $fieldDefinitions = $this->generateFieldDefinitions($fields);

        return "<?php\n\nclass $modelName {\n    protected \$table = '$tableName';\n\n    $fieldDefinitions\n}";
    }

    private function generateFieldDefinitions($fields)
    {
        $fieldLines = [];

        if (!empty($fields)) {
            $fieldList = explode(',', $fields);

            foreach ($fieldList as $field) {
                $fieldLines[] = "protected $$field;";
            }
        }

        return implode("\n    ", $fieldLines);
    }

    private function getOption(array &$args, $optionName, $defaultValue = null)
    {
        $key = array_search($optionName, $args);
        if ($key !== false && isset($args[$key + 1])) {
            $value = $args[$key + 1];
            // Remove the option and its value from the args array
            array_splice($args, $key, 2);
            return $value;
        }
        return $defaultValue;
    }
}
