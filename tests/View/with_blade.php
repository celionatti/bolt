<?php

declare(strict_types=1);

namespace Bolt\Bolt;

use Exception;
use Jenssegers\Blade\Blade; // You need to install the Blade template engine first

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
    private Blade $_blade;

    public function __construct($path = '')
    {
        $this->_defaultViewPath = $path;
        $this->_title = Config::get('title');
        $this->_blade = new Blade('/path/to/your/views', '/path/to/compiled/views'); // Adjust these paths accordingly
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

        // Render the view using Blade
        echo $this->_blade->make($path, $params)->render();
    }
}
