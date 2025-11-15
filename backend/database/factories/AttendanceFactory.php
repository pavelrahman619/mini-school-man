<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'date' => Carbon::today(),
            'status' => $this->faker->randomElement(['Present', 'Absent', 'Late']),
            'note' => $this->faker->optional(0.3)->sentence(),
            'recorded_by' => User::factory(),
        ];
    }
}
