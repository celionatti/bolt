<?php

declare(strict_types=1);

/**
 * ================================================
 * ================             ===================
 * Unique Validation
 * ================             ===================
 * ================================================
 */

namespace Bolt\Bolt\BoltValidation;

class UniqueValidation extends BoltValidator
{
    public function runValidation(): bool
    {
        $value = $this->_obj->{$this->field};
        if ($value == '' || !isset($value)) {
            return true;
        }

        // Define conditions to check for uniqueness
        $conditions = ["$this->field = :$this->field"];
        $bind = [":$this->field" => $value];

        //check updating record
        if (!$this->_obj->isNew()) {
            $conditions[] = "id != :id";
            $bind[':id'] = $this->_obj->primary_key;
        }

        //this allows you to check multiple fields for unique
        foreach ($this->additionalFieldData as $additionalField) {
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
}
