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
        $attributes = array_merge($this->definition(), $attributes);
        $model = new $this->model();
        return $model->create($attributes);
    }

    public function createMany(array $attributes = [])
    {
        $models = [];
        for ($i = 0; $i < $this->count; $i++) {
            $models[] = $this->create($attributes);
        }
        return $models;
    }

    abstract protected function definition(): array;

    public function count(int $count)
    {
        $this->count = $count;
        return $this;
    }

    // public function create(array $attributes = [])
    // {
    //     $models = $this->make($attributes);
    //     foreach ((array) $models as $model) {
    //         $model->save();
    //     }
    //     return $models;
    // }
}
