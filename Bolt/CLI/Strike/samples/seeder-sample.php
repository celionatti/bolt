<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============       ================
 * {CLASSNAME} Seeder 
 * ===============       ================
 * ======================================
 */

namespace Bolt\seeders;

use Faker\Factory;
use Bolt\Bolt\Seeder\BoltSeeder;

class {CLASSNAME} extends BoltSeeder
{
    /**
     * The Up method is to create table.
     *
     * @return void
     */
    public function seeding()
    {
        /**
         * Specify the table you want to seed
         */
        $this->table("users");

        /**
         * Faker Factory
         */
        $faker = Factory::create();

        $data = [
            'user_id' => 'random',
            'surname' => 'random',
            'othername' => 'random',
            'email' => 'random',
            'phone' => 'random',
            'avatar' => 'random',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'user',
            'gender' => $faker->randomElement(['Male', 'Female', 'Non-binary', 'Other']),
        ];

        $this->seed($data, 5);
    }
}