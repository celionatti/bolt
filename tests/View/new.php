<?php

namespace Bolt\Bolt;

use Exception;

class View_nn
{
    // ... (existing properties and methods)

    private string $_viewEngine; // Add this variable to store the view engine type

    public function __construct($path = '', $viewEngine = 'php')
    {
        $this->_defaultViewPath = $path;
        $this->_title = Config::get('title');
        $this->_viewEngine = $viewEngine; // Store the view engine type
    }

    // ... (existing methods)

    public function render($path = '', $params = []): void
    {
        if (empty($path)) {
            $path = $this->_defaultViewPath;
        }

        foreach ($params as $key => $value) {
            $$key = $value;
        }

        // Determine which view engine to use based on $this->_viewEngine
        switch ($this->_viewEngine) {
            case 'blade':
                // Render Blade template
                // You would need a Blade template engine library for this
                break;
            case 'twig':
                // Render Twig template
                // You would need a Twig template engine library for this
                break;
            case 'php':
            default:
                // Default to PHP view
                // $layoutPath = base_path('templates/layouts/' . $this->_layout . '.php');
                $layoutPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . 'layouts/' . $this->_layout . '.php');
                // $fullPath = base_path('templates' . DIRECTORY_SEPARATOR . $path . '.php');
                $fullPath = Bolt::$bolt->pathResolver->template_path(DIRECTORY_SEPARATOR . $path . '.php');

                if (!file_exists($fullPath)) {
                    throw new Exception("The view \"{$path}\" does not exist.");
                }
                if (!file_exists($layoutPath)) {
                    throw new Exception("The layout \"{$this->_layout}\" does not exist.");
                }

                require($layoutPath);
                break;
        }
    }
}




/**
 * OR
 */
 
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
 
     // ... (other methods remain the same)
 
     public function render($path = '', $params = []): void
     {
         if (empty($path)) {
             $path = $this->_defaultViewPath;
         }
 
         // Render the view using Blade
         echo $this->_blade->make($path, $params)->render();
     }
 }



//  composer require jenssegers/blade
 
