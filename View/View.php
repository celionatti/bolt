<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - View Class ==============
 * ================================
 */

namespace celionatti\Bolt\View;

use celionatti\Bolt\Bolt;
use celionatti\Bolt\BoltException\BoltException;

class View
{
    private string $_title = '';
    private string $_header = 'Dashboard';
    private array $_content = [];
    private $_buffer;
    private string $_layout;
    private array $_directives = [];

    public function __construct()
    {
        $this->registerDefaultDirectives();
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
        if (array_key_exists($key, $this->_content)) {
            echo $this->_content[$key];
        } else {
            echo '';
        }
    }

    public function partial($path, $params = []): void
    {
        $fullPath = Bolt::$bolt->pathResolver->template_path('partials' . DIRECTORY_SEPARATOR . $path . '.php');
        if (file_exists($fullPath)) {
            extract($params);
            include $fullPath;
        } else {
            throw new BoltException("Partial view not found: $fullPath", 404);
        }
    }

    public function render(string $path = '', array $params = [], bool $returnOutput = false): ?string
    {
        $output = '';

        try {
            $output = $this->renderBoltTemplate($path, $params);
        } catch (BoltException $e) {
            throw new BoltException($e->getMessage(), $e->getCode());
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

    private function renderBoltTemplate(string $path, array $params = []): string
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        $layoutPath = Bolt::$bolt->pathResolver->template_path('layouts' . DIRECTORY_SEPARATOR . $this->_layout . '.php');
        $fullPath = Bolt::$bolt->pathResolver->template_path("{$path}.php");

        if (!file_exists($fullPath)) {
            throw new BoltException("The view \"{$path}\" does not exist.");
        }
        if (!file_exists($layoutPath)) {
            throw new BoltException("The layout \"{$this->_layout}\" does not exist.");
        }

        $templateContent = file_get_contents($fullPath);
        $parsedContent = $this->renderTemplate($templateContent, $params);

        $layoutContent = file_get_contents($layoutPath);
        $parsedLayout = $this->renderTemplate($layoutContent, $params);

        ob_start();
        echo $parsedContent;
        echo $parsedLayout;
        return ob_get_clean();
    }

    private function renderTemplate($template, $data = [], $cache = false, $escapeOutput = true)
    {
        $parseTemplate = function ($template) use ($escapeOutput) {
            return preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function ($matches) use ($escapeOutput) {
                return $escapeOutput
                    ? "<?= htmlspecialchars({$matches[1]}, ENT_QUOTES, 'UTF-8') ?>"
                    : "<?= {$matches[1]} ?>";
            }, $template);
        };

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

        extract($data);

        ob_start();

        try {
            eval("?>{$parsedTemplate}");
        } catch (\Throwable $e) {
            ob_end_clean();
            throw new BoltException("Error rendering template: " . $e->getMessage());
        }

        return ob_get_clean();
    }

    private function registerDefaultDirectives()
    {
        $this->_directives['{{'] = function ($expression) {
            return '<?= htmlspecialchars(' . $expression . '); ?>';
        };

        $this->_directives['@if'] = function ($expression) {
            return '<?php if(' . $expression . '): ?>';
        };

        $this->_directives['@else'] = function () {
            return '<?php else: ?>';
        };

        $this->_directives['@elseif'] = function ($expression) {
            return '<?php elseif(' . $expression . '): ?>';
        };

        $this->_directives['@endif'] = function () {
            return '<?php endif; ?>';
        };

        $this->_directives['@csrf'] = function () {
            return '<input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">';
        };

        $this->_directives['@extends'] = function ($expression) {
            $this->_layout = trim($expression, '\'"');
            return '';
        };

        $this->_directives['@section'] = function ($expression) {
            return '<?php $this->start(' . $expression . '); ?>';
        };

        $this->_directives['@endsection'] = function () {
            return '<?php $this->end(); ?>';
        };

        $this->_directives['@yield'] = function ($expression) {
            return '<?= $this->content(' . $expression . '); ?>';
        };

        $this->_directives['@foreach'] = function ($expression) {
            return '<?php foreach(' . $expression . '): ?>';
        };

        $this->_directives['@endforeach'] = function () {
            return '<?php endforeach; ?>';
        };

        $this->_directives['@for'] = function ($expression) {
            return '<?php for(' . $expression . '): ?>';
        };

        $this->_directives['@endfor'] = function () {
            return '<?php endfor; ?>';
        };

        $this->_directives['@while'] = function ($expression) {
            return '<?php while(' . $expression . '): ?>';
        };

        $this->_directives['@endwhile'] = function () {
            return '<?php endwhile; ?>';
        };

        $this->_directives['@switch'] = function ($expression) {
            return '<?php switch(' . $expression . '): ?>';
        };

        $this->_directives['@case'] = function ($expression) {
            return '<?php case ' . $expression . ': ?>';
        };

        $this->_directives['@break'] = function () {
            return '<?php break; ?>';
        };

        $this->_directives['@endswitch'] = function () {
            return '<?php endswitch; ?>';
        };

        $this->_directives['@default'] = function () {
            return '<?php default: ?>';
        };

        $this->_directives['@auth'] = function () {
            return '<?php if(auth()->check()): ?>';
        };

        $this->_directives['@endauth'] = function () {
            return '<?php endif; ?>';
        };

        $this->_directives['@guest'] = function () {
            return '<?php if(auth()->guest()): ?>';
        };

        $this->_directives['@endguest'] = function () {
            return '<?php endif; ?>';
        };

        $this->_directives['@isset'] = function ($expression) {
            return '<?php if(isset(' . $expression . ')): ?>';
        };

        $this->_directives['@endisset'] = function () {
            return '<?php endif; ?>';
        };

        $this->_directives['@empty'] = function ($expression) {
            return '<?php if(empty(' . $expression . ')): ?>';
        };

        $this->_directives['@endempty'] = function () {
            return '<?php endif; ?>';
        };
    }
}
