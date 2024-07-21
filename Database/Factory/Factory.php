<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Factory =====================
 * ====================================
 */

namespace celionatti\Bolt\Database\Factory;


abstract class Factory
{
    protected $model;
    protected $count = 1;

    public function __construct()
    {
        $this->model = $this->getModelInstance();
    }

    abstract protected function getModelInstance();

    public function make(array $attributes = [])
    {
        $models = [];
        for ($i = 0; $i < $this->count; $i++) {
            $model = clone $this->model;
            foreach ($attributes as $key => $value) {
                $model->$key = $value;
            }
            $models[] = $model;
        }
        return $this->count === 1 ? $models[0] : $models;
    }

    public function create(array $attributes = [])
    {
        $models = $this->make($attributes);
        foreach ((array) $models as $model) {
            $model->save();
        }
        return $models;
    }

    public function count(int $count)
    {
        $this->count = $count;
        return $this;
    }
}
