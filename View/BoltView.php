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

use Exception;
use celionatti\Bolt\Bolt;
use Twig\Environment;
use Jenssegers\Blade\Blade;
use Twig\Loader\FilesystemLoader;


class BoltView
{
    private string $_title = '';
    private string $_header = 'Dashboard';
    private array $_content = [];
    private $_currentContent;
    private $_buffer;
    private string $_layout;
    private string $_defaultViewPath;
    private ?Blade $_blade = null;
    private ?Environment $_twig = null;

    public function __construct($path = '', $enableBlade = false, $enableTwig = false)
    {
        $this->_defaultViewPath = $path;

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
            throw new Exception("Your start method requires a valid key.");
        }
        $this->_buffer = $key;
        ob_start();
    }

    public function end(): void
    {
        if (empty($this->_buffer)) {
            throw new Exception("You must first run the start method.");
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
            include($fullPath);
        }
    }

    public function render($path = '', $params = []): void
    {
        if (empty($path)) {
            $path = $this->_defaultViewPath;
        }

        // Render using Blade if enabled
        if ($this->_blade) {
            echo $this->_blade->make($path, $params)->render();
        }
        // Render using Twig if enabled
        elseif ($this->_twig) {
            try {
                echo $this->_twig->render($path, $params);
            } catch (Exception $e) {
                echo 'Twig Error: ' . $e->getMessage();
            }
        }
        // Fallback to .php rendering if no template engine is enabled
        else {

            foreach ($params as $key => $value) {
                $$key = $value;
            }

            $layoutPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->_layout . '.php');
            $fullPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . $path . '.php');

            if (!file_exists($fullPath)) {
                throw new Exception("The view \"{$path}\" does not exist.");
            }
            if (!file_exists($layoutPath)) {
                throw new Exception("The layout \"{$this->_layout}\" does not exist.");
            }

            require($fullPath);
            require($layoutPath);
        }
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
}
