
# namespace App\Factories

use App\Models\User;

class UserFactory extends Factory
{
    protected function getModelInstance()
    {
        return new User();
    }

    public function definition()
    {
        return [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
        ];
    }

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => password_hash('password', PASSWORD_BCRYPT),
        ];
    }

    public function make(array $attributes = [])
    {
        $attributes = array_merge($this->definition(), $attributes);
        return parent::make($attributes);
    }
}

## Also you can use like this

<?php

use App\Factories\UserFactory;

// Create a User instance without saving to the database
$user = (new UserFactory())->make();

// Create a User instance and save it to the database
$createdUser = (new UserFactory())->create();

use App\Factories\UserFactory;

// Create 5 User instances and save them to the database
$users = (new UserFactory())->count(5)->create();
