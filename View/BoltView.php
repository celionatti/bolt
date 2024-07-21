<?php

declare(strict_types=1);

/**
 * ==================================================
 * ==================           =====================
 * ******** BoltView Class
 * ==================           =====================
 * ==================================================
 */

namespace celionatti\Bolt\View;

use Twig\Environment;
use celionatti\Bolt\Bolt;
use Jenssegers\Blade\Blade;
use Twig\Loader\FilesystemLoader;
use celionatti\Bolt\Helpers\Logger;
use celionatti\Bolt\BoltException\BoltException;


class BoltView
{
    private string $_title = '';
    private string $_header = 'Dashboard';
    private array $_content = [];
    private $_buffer;
    private string $_layout;
    private string $_defaultViewPath;
    private ?Blade $_blade = null;
    private ?Environment $_twig = null;
    private ?Logger $_logger = null;

    public function __construct($path = '', $enableBlade = false, $enableTwig = false, ?Logger $logger = null)
    {
        $this->_defaultViewPath = $path;
        $this->_logger = $logger;

        // Initialize Blade if enabled
        if ($enableBlade) {
            $this->initializeBlade();
        }

        // Initialize Twig if enabled
        if ($enableTwig) {
            $this->initializeTwig();
        }
    }

    public function setLayout($layout): void
    {
        $this->_layout = $layout;
    }

    public function setTitle($title): void
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getHeader()
    {
        return $this->_header;
    }

    public function setHeader($_header)
    {
        $this->_header = $_header;
    }

    public function start($key): void
    {
        if (empty($key)) {
            throw new BoltException("Your start method requires a valid key.");
        }
        $this->_buffer = $key;
        ob_start();
    }

    public function end(): void
    {
        if (empty($this->_buffer)) {
            throw new BoltException("You must first run the start method.");
        }
        $this->_content[$this->_buffer] = ob_get_clean();
        $this->_buffer = null;
    }

    public function content($key): void
    {
        // Create your functions and helpers.
        if (array_key_exists($key, $this->_content)) {
            echo $this->_content[$key];
        } else {
            echo '';
        }
    }

    public function partial($path, $params = []): void
    {
        $fullPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . $path . '.php');
        if (file_exists($fullPath)) {
            extract($params);
            include $fullPath;
        } else {
            $this->logError("Partial view not found: $fullPath");
        }
    }

    public function render(string $path = '', array $params = [], bool $returnOutput = false): ?string
    {
        if (empty($path)) {
            $path = $this->_defaultViewPath;
        }

        $output = '';

        try {
            // Render using Blade if enabled
            if ($this->_blade) {
                $output = $this->_blade->make($path, $params)->render();
            }
            // Render using Twig if enabled
            elseif ($this->_twig) {
                $output = $this->_twig->render($path, $params);
            }
            // Fallback to .php rendering if no template engine is enabled
            else {
                $output = $this->renderBoltTemplate($path, $params);
            }
        } catch (BoltException $e) {
            $this->logError('Rendering Error: ' . $e->getMessage());
            if (!$returnOutput) {
                echo 'Rendering Error: ' . $e->getMessage();
            }
        }

        if ($returnOutput) {
            return $output;
        } else {
            echo $output;
        }

        return null;
    }

    public function renderJson(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // private function renderBoltTemplate(string $path, array $params = []): string
    // {
    //     foreach ($params as $key => $value) {
    //         $$key = $value;
    //     }

    //     $layoutPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->_layout . '.php');
    //     $fullPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . $path . '.php');

    //     if (!file_exists($fullPath)) {
    //         throw new Exception("The view \"{$path}\" does not exist.");
    //     }
    //     if (!file_exists($layoutPath)) {
    //         throw new Exception("The layout \"{$this->_layout}\" does not exist.");
    //     }

    //     ob_start();
    //     require $fullPath;
    //     require $layoutPath;
    //     return ob_get_clean();
    // }

    private function renderBoltTemplate(string $path, array $params = []): string
    {
        // Extract params as variables
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        $layoutPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->_layout . '.php');
        $fullPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . $path . '.php');

        if (!file_exists($fullPath)) {
            throw new BoltException("The view \"{$path}\" does not exist.");
        }
        if (!file_exists($layoutPath)) {
            throw new BoltException("The layout \"{$this->_layout}\" does not exist.");
        }

        // Read and parse the template file
        $templateContent = file_get_contents($fullPath);
        $parsedContent = $this->renderTemplate($templateContent, $params);

        // Read and parse the layout file
        $layoutContent = file_get_contents($layoutPath);
        $parsedLayout = $this->renderTemplate($layoutContent, $params);

        ob_start();
        echo $parsedContent;
        echo $parsedLayout;
        return ob_get_clean();
    }

    // Function to replace {{ }}
    private function renderTemplate($template, $data = [], $cache = false, $escapeOutput = true)
    {
        $parseTemplate = function ($template) use ($escapeOutput) {
            return preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function ($matches) use ($escapeOutput) {
                return $escapeOutput
                    ? "<?= htmlspecialchars({$matches[1]}, ENT_QUOTES, 'UTF-8') ?>"
                    : "<?= {$matches[1]} ?>";
            }, $template);
        };

        // Optionally use cache
        $cacheKey = md5($template);
        $cacheDir = Bolt::$bolt->pathResolver->storage_path(DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);
        $cacheFile = "{$cacheDir}{$cacheKey}.php";

        if ($cache && file_exists($cacheFile)) {
            $parsedTemplate = file_get_contents($cacheFile);
        } else {
            $parsedTemplate = $parseTemplate($template);
            if ($cache) {
                if (!file_exists($cacheDir)) {
                    mkdir($cacheDir, 0777, true);
                }
                file_put_contents($cacheFile, $parsedTemplate);
            }
        }

        // Extract data to be used in the template
        extract($data);

        // Start output buffering
        ob_start();

        // Error handling
        try {
            eval("?>{$parsedTemplate}");
        } catch (\Throwable $e) {
            ob_end_clean();
            throw new BoltException("Error rendering template: " . $e->getMessage());
        }

        // Get the content from the buffer
        return ob_get_clean();
    }

    // Optional: Initialize Blade template engine
    private function initializeBlade()
    {
        $path = get_root_dir() . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "blade-views";
        $cache_path = get_root_dir() . DIRECTORY_SEPARATOR . "non-existence/cache" . DIRECTORY_SEPARATOR . "blade";
        $this->_blade = new Blade($path, $cache_path); // Adjust these paths accordingly
    }

    // Optional: Initialize Twig template engine
    private function initializeTwig()
    {
        $path = get_root_dir() . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "twig-views";
        $cache_path = get_root_dir() . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "twig";
        $loader = new FilesystemLoader($path); // Adjust this path accordingly
        // Configure Twig options
        $twigOptions = [
            // 'cache' => $cache_path, // Adjust the cache directory
            'cache' => false, // Adjust the cache directory
            'auto_reload' => true,       // Automatically reload the template if the source code changes (for development)
            'debug' => true,            // Enable debugging mode (shows detailed error messages)
            'strict_variables' => true, // Enforces strict variable access (throws an error for undefined variables)
            'autoescape' => 'html',      // Auto-escaping strategy ('html', 'js', 'css', 'url', 'html_attr', or false)
        ];
        $this->_twig = new Environment($loader, $twigOptions);
    }

    private function logError(string $message): void
    {
        if ($this->_logger) {
            $this->_logger->error($message);
        }
    }
}
