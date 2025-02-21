<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - ModelFactory ================
 * ====================================
 */

namespace celionatti\Bolt\Database\Model;

class ModelFactory
{
    protected string $modelClass;
    protected array $definitions = [];
    protected array $states = [];

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function define(array $attributes): self
    {
        $this->definitions = array_merge($this->definitions, $attributes);
        return $this;
    }

    public function state(array $state): self
    {
        $this->states[] = $state;
        return $this;
    }

    public function make(array $overrides = []): DatabaseModel
    {
        $attributes = array_merge(
            $this->definitions,
            ...$this->states,
            $overrides
        );

        return (new $this->modelClass())->fill($attributes);
    }

    public function create(array $overrides = []): DatabaseModel
    {
        $model = $this->make($overrides);
        $model->save();
        return $model;
    }
}