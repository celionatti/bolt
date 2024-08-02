<?php

declare(strict_types=1);

/**
 * ==================================================
 * ==================               =================
 * User Model Class
 * ==================               =================
 * ==================================================
 */

namespace celionatti\Bolt\Model;

use celionatti\Bolt\Model\Model;

class User extends Model
{
    // The table associated with the model.
    protected $table = 'users';

    // The primary key associated with the table.
    protected $primaryKey = 'user_id';

    // The attributes that are mass assignable.
    protected $fillable = ['name', 'email', 'password'];

    // The attributes that should be hidden for arrays.
    protected $hidden = ['password', 'remember_token'];

    // The attributes that should be cast to native types.
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hash',
    ];

    // The attributes that should be guarded from mass assignment.
    protected $guarded = ['id'];

    // Constructor to initialize the model with attributes
    public function __construct(array $attributes = [])
    {
        parent::__construct();
        $this->fill($attributes);
    }

    // Find user by ID
    public static function findUser($id)
    {
        $instance = new static();
        $conditions = [$instance->primaryKey => $id];
        $query = $instance->findBy($conditions);
        return $query;
    }

    // Find user by email
    public static function findByEmail($email)
    {
        return (new static())->findBy(['email' => $email]);
    }
}
