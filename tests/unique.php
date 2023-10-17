<?php

declare(strict_types=1);

namespace Bolt\Bolt\Database;

use Bolt\Bolt\BoltException\BoltException;
use Bolt\Bolt\BoltQueryBuilder\BoltQueryBuilder;
use Bolt\Bolt\Model;

abstract class DatabaseModel extends Model
{
    // ... (existing properties and methods)

    // Add a method for unique validation
    protected function isUnique($field, $value, $additionalFieldData = [])
    {
        if (empty($value)) {
            return true; // Empty values are considered unique.
        }

        // Define conditions to check for uniqueness
        $conditions = ["$field = :$field"];
        $bind = [":$field" => $value];

        // Check if it's an update operation
        if (!$this->isNew()) {
            $conditions[] = "id != :id";
            $bind[':id'] = $this->id;
        }

        // Check additional fields for uniqueness
        foreach ($additionalFieldData as $additionalField) {
            $conditions[] = "$additionalField = :$additionalField";
            $bind[":$additionalField"] = $this->{$additionalField};
        }

        // Build the query parameters
        $queryParams = [
            'conditions' => implode(' AND ', $conditions),
            'bind' => $bind,
        ];

        // Execute the query to check for uniqueness
        $exists = $this->getQueryBuilder()->select()->where($queryParams['conditions'], $queryParams['bind'])->get();

        return empty($exists);
    }

    // ... (other methods)

    public function beforeSave(): void
    {
        // Add your custom validation logic here
        if (!$this->isUnique($this->field, $this->_obj->{$this->field}, $this->additionalFieldData)) {
            $this->errors[] = "The value is not unique.";
        }
    }
}
