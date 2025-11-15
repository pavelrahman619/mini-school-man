<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            ['class' => 'Class 10A', 'section' => 'A'],
            ['class' => 'Class 10B', 'section' => 'B'],
            ['class' => 'Class 11A', 'section' => 'A'],
        ];

        $studentCounter = 1;

        foreach ($classes as $classInfo) {
            // Create 10 students per class (30 total)
            for ($i = 1; $i <= 10; $i++) {
                Student::create([
                    'name' => "Student {$studentCounter}",
                    'student_id' => 'STU' . str_pad($studentCounter, 4, '0', STR_PAD_LEFT),
                    'class' => $classInfo['class'],
                    'section' => $classInfo['section'],
                    'photo' => null,
                ]);
                $studentCounter++;
            }
        }
    }
}
