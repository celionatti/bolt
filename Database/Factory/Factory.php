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
        $this->model = $this->getModelInstance();
        $this->faker = FakerFactory::create();
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
        $data = array_merge($this->definition(), $attributes);
        return $this->model->create($data);
    }

    public function createMany(array $attributes = [])
    {
        $models = [];
        for ($i = 0; $i < $this->count; $i++) {
            $models[] = $this->create($attributes);
        }
        return $models;
    }

    public function definition()
    {
        return [];
    }

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
