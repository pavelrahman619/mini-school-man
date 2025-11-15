<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'student_id' => 'STU' . $this->faker->unique()->numberBetween(1000, 9999),
            'class' => $this->faker->randomElement(['10A', '10B', '11A', '11B', '12A']),
            'section' => $this->faker->randomElement(['A', 'B', 'C']),
            'photo' => null,
        ];
    }
}
