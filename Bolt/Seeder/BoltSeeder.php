<?php

declare(strict_types=1);

/**
 * =====================================================
 * =================                ====================
 * Bolt Seeder
 * =================                ====================
 * =====================================================
 */

namespace Bolt\Bolt\Seeder;

use Faker\Factory;
use Bolt\Bolt\Database\Database;
use Bolt\Bolt\BoltException\BoltException;

class BoltSeeder extends Database
{
    private $table;
    private $faker;

    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
    }

    public function table(string $tableName): self
    {
        $this->table = $tableName;
        return $this;
    }

    public function seed(array $data, int $count = 1)
    {
        if (!is_array($data) || empty($data)) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $record = $this->generateRecord($data);
            $this->insertRecord($record);
        }
    }

    private function generateRecord(array $data): array
    {
        $record = [];
        foreach ($data as $column => $generator) {
            if (is_callable($generator)) {
                $record[$column] = $generator();
            } elseif ($generator === 'random') {
                $record[$column] = $this->generateRandomValue($column);
            } else {
                $record[$column] = $generator;
            }
        }

        return $record;
    }

    private function generateRandomValue(string $column)
    {
        switch ($column) {
            case 'user_id':
                return $this->faker->uuid;
            case 'uid':
                return $this->faker->uuid;
            case 'name':
                return $this->faker->name;
            case 'surname':
                return $this->faker->lastName;
            case 'firstname':
                return $this->faker->firstName;
            case 'lastname':
                return $this->faker->lastName;
            case 'othername':
                return $this->faker->name;
            case 'email':
                return $this->faker->unique()->email;
            case 'phone':
                return $this->faker->phoneNumber;
            case 'avatar':
                return $this->faker->imageUrl(200, 200, 'people');
            case 'image':
                return $this->faker->imageUrl(200, 200, 'technics');
            case 'password':
                return $this->faker->password;
            case 'address':
                return $this->faker->address;
            case 'city':
                return $this->faker->city;
            case 'country':
                return $this->faker->country;
            case 'job':
                return $this->faker->jobTitle;
            case 'company':
                return $this->faker->company;
            case 'color':
                return $this->faker->hexColor;
            case 'birthdate':
                return $this->faker->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d');
            case 'description':
                return $this->faker->realText(200);
                // Add more cases for other columns if needed
            default:
                return $this->faker->text(50);
        }
    }

    private function insertRecord(array $data)
    {
        try {
            $columns = array_keys($data);
            $columnNames = implode(', ', $columns);
            $valuePlaceholders = implode(', ', array_map(function ($col) {
                return ":$col";
            }, $columns));
            $sql = "INSERT INTO $this->table ($columnNames) VALUES ($valuePlaceholders)";

            $stmt = $this->prepare($sql);
            $stmt->execute($data);
        } catch (BoltException $e) {
            throw $e;
        }
    }
}
