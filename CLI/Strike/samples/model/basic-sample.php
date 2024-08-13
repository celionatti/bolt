<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============        ===============
 * ===== {CLASSNAME} Model
 * ===============        ===============
 * ======================================
 */

namespace PhpStrike\app\models;

use celionatti\Bolt\Model\Model;
use celionatti\Bolt\Database\Relationships\HasMany;

class {CLASSNAME} extends Model
{
    /** By default the table name is the the classname with s added. But if different you can define it. */
    $this->table = "{TABLENAME}";
    
    protected $fillable = ['user_id', 'name', 'email', 'password', 'remember_token'];

    // protected $guarded = ['user_id'];

    protected $casts = ['password' => 'hash'];

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }
}