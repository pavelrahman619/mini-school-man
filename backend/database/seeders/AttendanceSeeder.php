<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $teacher = User::where('role', 'teacher')->first();
        $statuses = ['Present', 'Absent', 'Late'];

        // Create attendance for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');

            foreach ($students as $student) {
                // Randomly assign status with higher probability for Present
                $random = rand(1, 100);
                if ($random <= 80) {
                    $status = 'Present';
                } elseif ($random <= 90) {
                    $status = 'Late';
                } else {
                    $status = 'Absent';
                }

                Attendance::create([
                    'student_id' => $student->id,
                    'date' => $date,
                    'status' => $status,
                    'note' => $status === 'Absent' ? 'Sample note' : null,
                    'recorded_by' => $teacher->id,
                ]);
            }
        }
    }
}
