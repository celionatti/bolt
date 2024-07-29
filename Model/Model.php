<?php

declare(strict_types=1);

/**
 * ==================================================
 * ==================               =================
 * Model Class
 * ==================               =================
 * ==================================================
 */

namespace celionatti\Bolt\Model;

use celionatti\Bolt\Database\Model\DatabaseModel;

class Model extends DatabaseModel
{
    // Fill the model with an array of attributes.
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->__set($key, $value);
        }
        return $this;
    }
}
