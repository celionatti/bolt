<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - Component Class ==============
 * =====================================
 */

namespace celionatti\Bolt\Component;

use celionatti\Bolt\Bolt;
use celionatti\Bolt\BoltException\BoltException;

abstract class Component
{
    protected $data = [];
    protected $slots = [];
    
    public function __construct(array $data = [], array $slots = [])
    {
        $this->data = $data;
        $this->slots = $slots;
        $this->mount();
    }

    protected function mount()
    {
        // Lifecycle method called when the component is instantiated
    }

    public function withData(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function withSlots(array $slots)
    {
        $this->slots = $slots;
        return $this;
    }

    public function render()
    {
        $viewPath = $this->getViewPath();
        if (file_exists($viewPath)) {
            ob_start();
            extract($this->data);
            include $viewPath;
            return ob_get_clean();
        }
        throw new BoltException("View file for component not found: {$viewPath}");
    }

    protected function slot(string $name, string $default = '')
    {
        return $this->slots[$name] ?? $default;
    }

    protected function getViewPath(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        $viewFileName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $className));
        return Bolt::$bolt->pathResolver->template_path("components" . DIRECTORY_SEPARATOR . $viewFileName . '.php');
    }
}
