<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - View Class ==============
 * ================================
 */

namespace celionatti\Bolt\View;

use celionatti\Bolt\Bolt;

class View
{
    protected $viewsPath;
    protected $cachePath;
    protected $cacheEnabled;
    protected $directives = [];
    protected $components = [];
    protected $data = [];
    protected $sections = [];
    protected $sectionStack = [];
    protected $extends;

    public function __construct($viewsPath, $cachePath = null, $cacheEnabled = false)
    {
        $this->viewsPath = $viewsPath;
        $this->cachePath = $cachePath;
        $this->cacheEnabled = $cacheEnabled;

        // Register default directives
        $this->registerDefaultDirectives();
    }

    protected function registerDefaultDirectives()
    {
        // Echo directive
        $this->directives['{{'] = function ($expression) {
            return '<?= htmlspecialchars(' . $expression . '); ?>';
        };

        // If directive
        $this->directives['@if'] = function ($expression) {
            return '<?php if(' . $expression . '): ?>';
        };

        // Else directive
        $this->directives['@else'] = function () {
            return '<?php else: ?>';
        };

        // Elseif directive
        $this->directives['@elseif'] = function ($expression) {
            return '<?php elseif(' . $expression . '): ?>';
        };

        // Endif directive
        $this->directives['@endif'] = function () {
            return '<?php endif; ?>';
        };

        // CSRF directive
        $this->directives['@csrf'] = function () {
            return '<input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">';
        };

        // Extends directive
        $this->directives['@extends'] = function ($expression) {
            $this->extends = trim($expression, '\'"');
            return '';
        };

        // Section directive
        $this->directives['@section'] = function ($expression) {
            return '<?php $this->startSection(' . $expression . '); ?>';
        };

        // Endsection directive
        $this->directives['@endsection'] = function () {
            return '<?php $this->endSection(); ?>';
        };

        // Yield directive
        $this->directives['@yield'] = function ($expression) {
            return '<?= $this->yieldContent(' . $expression . '); ?>';
        };
    }

    public function registerDirective($name, callable $handler)
    {
        $this->directives[$name] = $handler;
    }

    public function render($view, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        $this->sections = [];
        $this->sectionStack = [];
        $this->extends = null;

        $viewPath = $this->viewsPath . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("View [$view] not found.");
        }

        $compiledView = $this->compileView($viewPath);

        if ($this->extends) {
            $layoutPath = $this->viewsPath . '/' . str_replace('.', '/', $this->extends) . '.php';
            if (!file_exists($layoutPath)) {
                throw new \Exception("Layout [$this->extends] not found.");
            }
            $compiledLayout = $this->compileView($layoutPath);
            $compiledView = $compiledLayout;
        }

        if ($this->cacheEnabled) {
            $cacheFile = $this->cachePath . '/' . md5($view) . '.php';
            if (!file_exists($cacheFile) || filemtime($cacheFile) < filemtime($viewPath)) {
                file_put_contents($cacheFile, $compiledView);
            }
            $this->evaluateView($cacheFile, $this->data);
        } else {
            $this->evaluateViewString($compiledView, $this->data);
        }
    }

    protected function compileView($viewPath)
    {
        $content = file_get_contents($viewPath);

        // Handle directives
        foreach ($this->directives as $directive => $handler) {
            $content = preg_replace_callback('/' . preg_quote($directive) . '\s*(\(.*?\))?/', function ($matches) use ($handler) {
                return $handler(isset($matches[1]) ? trim($matches[1], '()') : null);
            }, $content);
        }

        return $content;
    }

    protected function evaluateView($file, $data)
    {
        extract($data, EXTR_SKIP);
        include $file;
    }

    protected function evaluateViewString($viewString, $data)
    {
        extract($data, EXTR_SKIP);
        eval('?>' . $viewString);
    }

    // Methods for handling components and partials
    public function component($name, $componentPath)
    {
        $this->components[$name] = $componentPath;
    }

    public function renderComponent($name, $data = [])
    {
        if (!isset($this->components[$name])) {
            throw new \Exception("Component [$name] not found.");
        }

        $componentPath = $this->components[$name];
        return $this->render($componentPath, $data);
    }

    public function partial($view, $data = [])
    {
        return $this->render($view, $data);
    }

    // Methods for handling sections and layout
    protected function startSection($name)
    {
        if (ob_start()) {
            $this->sectionStack[] = $name;
        }
    }

    protected function endSection()
    {
        $last = array_pop($this->sectionStack);
        $this->sections[$last] = ob_get_clean();
    }

    protected function yieldContent($name)
    {
        return $this->sections[$name] ?? '';
    }
}
