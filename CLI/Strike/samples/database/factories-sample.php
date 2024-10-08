<?php

namespace PhpStrike\database\Factories;

use celionatti\Bolt\Database\Factory\Factory;
use PhpStrike\app\models\User;

class {CLASSNAME} extends Factory
{
    protected $model = {CLASSNAME}::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        return [
            'user_id' => bolt_uuid(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'remember_token' => stringToken(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
