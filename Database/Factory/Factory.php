<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Factory =====================
 * ====================================
 */

namespace celionatti\Bolt\Database\Factory;

use Faker\Factory as FakerFactory;

abstract class Factory
{
    protected $model;
    protected $count = 1;
    protected $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
    }

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
        $models = [];
        for ($i = 0; $i < $this->count; $i++) {
            $modelAttributes = array_merge($this->definition(), $attributes);
            $model = new $this->model();
            $models[] = $model->create($modelAttributes);
        }

        return $this->count === 1 ? $models[0] : $models;
    }

    public function createMany(array $attributes = [])
    {
        return $this->count($this->count)->create($attributes);
    }

    abstract protected function definition(): array;

    public function count(int $count)
    {
        $this->count = $count;
        return $this;
    }
}
