<?php

declare(strict_types=1);

/**
 * ======================================
 * Users =================
 * ======================================
 */

namespace Bolt\models;

use Bolt\Bolt\BoltValidation\RequiredValidation;
use Bolt\Bolt\Database\DatabaseModel;

class Users extends DatabaseModel
{
    public static function tableName(): string
    {
        return 'users';
    }

    public function beforeSave(): void
    {
        $this->runValidation(new RequiredValidation($this, ['field' => 'surname', 'msg' => "Surname is a required field."]));
    }
}