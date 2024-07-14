<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - Collection ==============
 * ================================
 */

namespace celionatti\Bolt\Illuminate;

use Traversable;
use ArrayIterator;
use IteratorAggregate;

class Collection implements IteratorAggregate
{
    protected $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function map(callable $callback)
    {
        return new static(array_map($callback, $this->items));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}