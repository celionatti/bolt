<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============        ===============
 * ===== {{CLASSNAME}} Model
 * ===============        ===============
 * ======================================
 */

namespace {{NAMESPACE}};

use celionatti\Bolt\Model\Model;

class {{CLASSNAME}} extends Model
{
    /** By default the table name is the the classname with s added. But if different you can define it. */
    protected $table = "{{TABLENAME}}";
    
    protected $fillable = ['user_id', 'name', 'email', 'password', 'remember_token'];

    // protected $guarded = ['user_id'];

    protected $casts = ['password' => 'hash'];
}