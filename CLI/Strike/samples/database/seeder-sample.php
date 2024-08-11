<?php

namespace PhpStrike\database\seeders;

use celionatti\Bolt\models\User;
use celionatti\Bolt\Database\Seeder\Seeder;

class {CLASSNAME} extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory()->create();
        // User::factory()->count(20)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
