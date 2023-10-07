<?php

declare(strict_types=1);

namespace Bolt\Bolt;

use Exception;
use Jenssegers\Blade\Blade; // Optional: Blade template engine
use Twig\Environment; // Optional: Twig template engine
use Twig\Loader\FilesystemLoader; // Optional: Twig template engine

class View
{
    private string $_title = '';
    private string $_header = 'Dashboard';
    private string $_metaTitle = '';
    private string $_metaDescription = '';
    private string $_metaKeywords = '';
    private string $_author = '';
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
        $this->_title = Config::get('title');

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

    public function setMetaTitle($metaTitle): void
    {
        $this->_metaTitle = $metaTitle;
    }

    public function setMetaDescription($metaDescription): void
    {
        $this->_metaDescription = $metaDescription;
    }

    public function setMetaKeywords($metaKeywords): void
    {
        $this->_metaKeywords = $metaKeywords;
    }

    public function setAuthor($author): void
    {
        $this->_author = $author;
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
            echo $this->_twig->render($path, $params);
        }
        // Fallback to .php rendering if no template engine is enabled
        else {
            // $layoutPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->_layout . '.php');
            $layoutPath = base_path('templates' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->_layout . '.php');
            // $fullPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . $path . '.php');
            $fullPath = base_path('templates' . DIRECTORY_SEPARATOR . $path . '.php');

            if (!file_exists($fullPath)) {
                throw new Exception("The view \"{$path}\" does not exist.");
            }
            if (!file_exists($layoutPath)) {
                throw new Exception("The layout \"{$this->_layout}\" does not exist.");
            }

            require($layoutPath);
        }
    }

    // Optional: Initialize Blade template engine
    private function initializeBlade()
    {
        $this->_blade = new Blade('/path/to/your/views', '/path/to/compiled/views'); // Adjust these paths accordingly
    }

    // Optional: Initialize Twig template engine
    private function initializeTwig()
    {
        $loader = new FilesystemLoader('/path/to/your/twig/templates'); // Adjust this path accordingly
        $this->_twig = new Environment($loader, [
            // Twig configuration options here
        ]);
    }
}
