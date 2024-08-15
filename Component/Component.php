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
    protected array $data = [];
    protected array $slots = [];

    public function __construct(array $data = [], array $slots = [])
    {
        $this->data = $data;
        $this->slots = $slots;
        $this->mount();
    }

    /**
     * Lifecycle method called when the component is instantiated.
     * Can be overridden by the child component.
     */
    protected function mount(): void
    {
        // Default mount logic, override in child components as needed
    }

    /**
     * Merge additional data into the component's data array.
     *
     * @param array $data
     * @return $this
     */
    public function withData(array $data): static
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Merge additional slots into the component's slots array.
     *
     * @param array $slots
     * @return $this
     */
    public function withSlots(array $slots): static
    {
        $this->slots = array_merge($this->slots, $slots);
        return $this;
    }

    /**
     * Render the component by including the corresponding view file.
     *
     * @throws BoltException if the view file is not found.
     * @return string
     */
    public function render(): string
    {
        $viewPath = $this->getViewPath();

        if (!file_exists($viewPath)) {
            throw new BoltException("View file for component not found: {$viewPath}");
        }

        ob_start();
        extract($this->data);
        include $viewPath;
        return ob_get_clean();
    }

    /**
     * Retrieve the slot content by name, with an optional default value.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    protected function slot(string $name, string $default = ''): string
    {
        return $this->slots[$name] ?? $default;
    }

    /**
     * Generate the file path for the component's view file based on the class name.
     *
     * @return string
     */
    protected function getViewPath(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        $viewFileName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $className));
        return Bolt::$bolt->pathResolver->template_path("components" . DIRECTORY_SEPARATOR . "{$viewFileName}.php");
    }
}
