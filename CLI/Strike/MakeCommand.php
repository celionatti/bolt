<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Make commands =============
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CommandInterface;
use celionatti\Bolt\CLI\CliActions;
use RuntimeException;

class MakeCommand extends CliActions implements CommandInterface
{
    private const RESOURCE_TYPES = [
        'controller' => [
            'description' => 'Create a new controller class',
            'subfolder' => 'app/controllers',
            'suffix' => 'Controller'
        ],
        'model' => [
            'description' => 'Create a new model class',
            'subfolder' => 'app/models',
            'suffix' => ''
        ],
        'middleware' => [
            'description' => 'Create a new middleware class',
            'subfolder' => 'app/middlewares',
            'suffix' => 'Middleware'
        ],
        'service' => [
            'description' => 'Create a new service class',
            'subfolder' => 'app/providers',
            'suffix' => 'ServiceProvider'
        ],
        'component' => [
            'description' => 'Create a new component',
            'subfolder' => 'app/components',
            'suffix' => 'Component'
        ],
        'view' => [
            'description' => 'Create a new view file',
            'subfolder' => 'resources/views',
            'suffix' => ''
        ],
        'layout' => [
            'description' => 'Create a new layout file',
            'subfolder' => 'resources/views/layouts',
            'suffix' => ''
        ]
    ];

    private const VIEW_ENGINES = [
        'php' => '.php',
        'blade' => '.blade.php',
        'twig' => '.twig'
    ];

    public function execute(array $args, array $options = []): void
    {
        $type = $args[0] ?? null;

        if (!$type || !isset(self::RESOURCE_TYPES[$type])) {
            $this->showAvailableTypes();
            return;
        }

        $methodName = 'create' . ucfirst($type);
        if (method_exists($this, $methodName)) {
            $this->$methodName($args, $options);
        } else {
            $this->createGenericResource($type, $options);
        }
    }

    public function getHelp(): string
    {
        return implode(PHP_EOL, [
            "Usage: make <type> [options]",
            "Available types:",
            ...array_map(
                fn($type, $config) => "  {$type}: {$config['description']}",
                array_keys(self::RESOURCE_TYPES),
                self::RESOURCE_TYPES
            ),
            "Options:",
            "  --force    Overwrite existing files",
        ]);
    }

    private function createController(array $args, array $options): void
    {
        $name = $this->promptResourceName('controller');
        $className = $this->pascalCase($name) . 'Controller';
        $path = "app/controllers/{$className}.php";

        // Ask for controller type
        $controllerType = $this->choice(
            "Select controller type:",
            [
                'basic' => 'Basic controller with common methods',
                'resource' => 'Resource controller with CRUD methods',
                'api' => 'API controller with JSON responses',
                'empty' => 'Empty controller structure'
            ],
            'empty'
        );

        $templatePath = "controller/{$controllerType}";
        $replacements = [
            'CLASSNAME' => $className,
            'NAMESPACE' => 'App\\Controllers',
            'BASE_CONTROLLER' => 'BaseController',
            'MODEL_NAME' => $this->pascalCase($name),
            'VIEW_PATH' => strtolower($name)
        ];

        $this->createFromTemplate(
            $templatePath,
            $path,
            $replacements,
            $options
        );

        // If it's a resource controller, ask to create associated view files
        if ($controllerType === 'resource' && $this->confirm("Create associated view files?")) {
            $this->createResourceViews($name, $options);
        }
    }

    private function createGenericResource(string $type, array $options): void
    {
        $config = self::RESOURCE_TYPES[$type];
        $name = $this->promptResourceName($type);
        $className = $this->pascalCase($name) . $config['suffix'];

        $path = "{$config['subfolder']}/{$className}.php";

        $this->createFromTemplate(
            $type,
            $path,
            [
                'CLASSNAME' => $className,
                'NAMESPACE' => $this->generateNamespace($config['subfolder'])
            ],
            $options
        );
    }

    private function createModel(array $args, array $options): void
    {
        $name = $this->promptResourceName('model');
        $className = $this->pascalCase($name);
        $path = "app/models/{$className}.php";

        $templateType = $this->choice(
            "Select model type:",
            [
                'empty' => 'Empty model structure',
                'basic' => 'Basic model with common methods'
            ],
            'basic'
        );

        $this->createFromTemplate(
            "model/{$templateType}",
            $path,
            [
                'CLASSNAME' => $className,
                'TABLENAME' => strtolower($className . 's'),
                'NAMESPACE' => 'App\\Models'
            ],
            $options
        );

        if ($this->confirm("Create migration for this model?")) {
            $this->createMigration($name, $options);
        }
    }

    private function createView(array $args, array $options): void
    {
        $this->createViewResource('view', $options);
    }

    private function createLayout(array $args, array $options): void
    {
        $this->createViewResource('layout', $options);
    }

    private function createResourceViews(string $name, array $options): void
    {
        $viewPath = 'resources/views/' . strtolower($name);
        $views = ['index', 'create', 'edit', 'show'];

        foreach ($views as $view) {
            $this->createFromTemplate(
                'view/resource',
                "{$viewPath}/{$view}.php",
                [
                    'VIEW_TITLE' => ucfirst($name) . ' ' . ucfirst($view),
                    'RESOURCE_NAME' => $name
                ],
                $options
            );
        }

        $this->message("Created resource views in: {$viewPath}", 'success');
    }

    private function createViewResource(string $type, array $options): void
    {
        $name = $this->promptResourceName($type);
        $engine = $this->choice(
            "Select template engine:",
            self::VIEW_ENGINES,
            'php'
        );

        $subfolder = $this->prompt("Enter subfolder path (optional)");
        $config = self::RESOURCE_TYPES[$type];

        $path = $config['subfolder'] . '/' .
                ($subfolder ? trim($subfolder, '/') . '/' : '') .
                "{$name}" . self::VIEW_ENGINES[$engine];

        $this->createFromTemplate(
            "view/{$type}",
            $path,
            ['CONTENT' => "<h1>{$name}</h1>"],
            $options
        );
    }

    private function createMigration(string $modelName, array $options): void
    {
        $className = 'Create' . $this->pascalCase($modelName) . 'Table';
        $fileName = date('Y_m_d_His') . "_create_{$modelName}_table.php";
        $path = "database/migrations/{$fileName}";

        $this->createFromTemplate(
            'migration',
            $path,
            [
                'CLASSNAME' => $className,
                'TABLENAME' => strtolower($modelName . 's')
            ],
            $options
        );
    }

    private function createFromTemplate(string $template, string $path, array $replacements, array $options): void
    {
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . ltrim($path, '/');
        $templatePath = __DIR__ . "/samples/{$template}.php";

        if (!file_exists($templatePath)) {
            throw new RuntimeException("Template not found: {$template}");
        }

        if (file_exists($fullPath) && !($options['force'] ?? false)) {
            $this->message("File already exists: {$path}", 'warning');
            return;
        }

        $content = strtr(
            file_get_contents($templatePath),
            array_map(
                fn($value) => (string)$value,
                array_combine(
                    array_map(fn($key) => "{{{$key}}}", array_keys($replacements)),
                    $replacements
                )
            )
        );

        $this->ensureDirectoryExists(dirname($fullPath));

        if (file_put_contents($fullPath, $content) === false) {
            throw new RuntimeException("Failed to create file: {$path}");
        }

        $this->message("Created successfully: {$path}", 'success');
    }

    private function showAvailableTypes(): void
    {
        $this->message("Available resource types:", 'info');
        foreach (self::RESOURCE_TYPES as $type => $config) {
            $this->output(sprintf(
                "  %s%-12s%s %s",
                self::COLORS['primary'],
                $type,
                self::COLORS['reset'],
                $config['description']
            ));
        }
    }

    private function promptResourceName(string $type): string
    {
        while (true) {
            $name = $this->prompt("Enter {$type} name");
            $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);

            if (!empty($name)) {
                return $name;
            }

            $this->message(
                "Invalid name. Please use only letters, numbers and underscores",
                'warning'
            );
        }
    }

    private function generateNamespace(string $path): string
    {
        $namespace = str_replace(
            ['app/', '/'],
            ['App\\', '\\'],
            $path
        );
        return rtrim($namespace, '\\');
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            throw new RuntimeException("Failed to create directory: {$path}");
        }
    }
}
