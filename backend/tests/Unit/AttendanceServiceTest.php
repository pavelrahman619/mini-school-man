<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AttendanceService;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use App\Events\AttendanceRecorded;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendanceService = new AttendanceService();
    }

    /**
     * Test that bulk attendance is recorded successfully.
     */
    public function test_records_bulk_attendance_successfully(): void
    {
        Event::fake();

        // Create test data
        $user = User::factory()->create();
        $student1 = Student::factory()->create(['class' => '10A']);
        $student2 = Student::factory()->create(['class' => '10A']);

        $attendanceData = [
            [
                'student_id' => $student1->id,
                'date' => now()->format('Y-m-d'),
                'status' => 'Present',
                'note' => null,
            ],
            [
                'student_id' => $student2->id,
                'date' => now()->format('Y-m-d'),
                'status' => 'Absent',
                'note' => 'Sick leave',
            ],
        ];

        // Record bulk attendance
        $result = $this->attendanceService->recordBulkAttendance($attendanceData, $user);

        // Assert that 2 records were created
        $this->assertCount(2, $result);
        $this->assertDatabaseCount('attendances', 2);

        // Assert first attendance record
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student1->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'Present',
            'recorded_by' => $user->id,
        ]);

        // Assert second attendance record
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student2->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'Absent',
            'note' => 'Sick leave',
            'recorded_by' => $user->id,
        ]);

        // Assert event was dispatched
        Event::assertDispatched(AttendanceRecorded::class);
    }

    /**
     * Test that duplicate attendance for the same date is prevented.
     */
    public function test_prevents_duplicate_attendance_for_same_date(): void
    {
        Redis::shouldReceive('get')->andReturn(null);
        Redis::shouldReceive('setex')->andReturn(true);
        Redis::shouldReceive('del')->andReturn(1);
        Redis::shouldReceive('keys')->andReturn([]);

        $user = User::factory()->create();
        $student = Student::factory()->create(['class' => '10A']);
        $date = now()->format('Y-m-d');

        // Create initial attendance
        Attendance::create([
            'student_id' => $student->id,
            'date' => $date,
            'status' => 'Present',
            'recorded_by' => $user->id,
        ]);

        $this->assertDatabaseCount('attendances', 1);

        // Attempt to record duplicate attendance with different status
        $attendanceData = [
            [
                'student_id' => $student->id,
                'date' => $date,
                'status' => 'Absent',
                'note' => 'Changed status',
            ],
        ];

        $result = $this->attendanceService->recordBulkAttendance($attendanceData, $user);

        // Assert still only 1 record exists (updated, not duplicated)
        $this->assertDatabaseCount('attendances', 1);
        $this->assertCount(1, $result);

        // Assert the record was updated
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'date' => $date,
            'status' => 'Absent',
            'note' => 'Changed status',
            'recorded_by' => $user->id,
        ]);

        // Assert old status is gone
        $this->assertDatabaseMissing('attendances', [
            'student_id' => $student->id,
            'date' => $date,
            'status' => 'Present',
        ]);
    }

    /**
     * Test that monthly report is generated with correct calculations.
     */
    public function test_generates_monthly_report_with_correct_calculations(): void
    {
        $user = User::factory()->create();
        $class = '10A';
        
        // Create students
        $student1 = Student::factory()->create([
            'name' => 'John Doe',
            'student_id' => 'STU001',
            'class' => $class,
        ]);
        
        $student2 = Student::factory()->create([
            'name' => 'Jane Smith',
            'student_id' => 'STU002',
            'class' => $class,
        ]);

        $month = now()->month;
        $year = now()->year;

        // Create attendance records for student1 (5 days: 4 present, 1 late)
        for ($i = 1; $i <= 4; $i++) {
            Attendance::create([
                'student_id' => $student1->id,
                'date' => now()->setDay($i)->format('Y-m-d'),
                'status' => 'Present',
                'recorded_by' => $user->id,
            ]);
        }
        
        Attendance::create([
            'student_id' => $student1->id,
            'date' => now()->setDay(5)->format('Y-m-d'),
            'status' => 'Late',
            'recorded_by' => $user->id,
        ]);

        // Create attendance records for student2 (5 days: 3 present, 2 absent)
        for ($i = 1; $i <= 3; $i++) {
            Attendance::create([
                'student_id' => $student2->id,
                'date' => now()->setDay($i)->format('Y-m-d'),
                'status' => 'Present',
                'recorded_by' => $user->id,
            ]);
        }
        
        for ($i = 4; $i <= 5; $i++) {
            Attendance::create([
                'student_id' => $student2->id,
                'date' => now()->setDay($i)->format('Y-m-d'),
                'status' => 'Absent',
                'recorded_by' => $user->id,
            ]);
        }

        // Generate monthly report
        $report = $this->attendanceService->generateMonthlyReport($month, $year, $class);

        // Assert report structure
        $this->assertArrayHasKey('month', $report);
        $this->assertArrayHasKey('year', $report);
        $this->assertArrayHasKey('class', $report);
        $this->assertArrayHasKey('students', $report);
        $this->assertArrayHasKey('summary', $report);

        // Assert report values
        $this->assertEquals($month, $report['month']);
        $this->assertEquals($year, $report['year']);
        $this->assertEquals($class, $report['class']);
        $this->assertCount(2, $report['students']);

        // Find student1 in report
        $student1Report = collect($report['students'])->firstWhere('student_id', 'STU001');
        $this->assertNotNull($student1Report);
        $this->assertEquals(4, $student1Report['present']);
        $this->assertEquals(0, $student1Report['absent']);
        $this->assertEquals(1, $student1Report['late']);
        $this->assertEquals(5, $student1Report['total_days']);
        $this->assertEquals(80.0, $student1Report['percentage']); // 4/5 * 100 = 80%

        // Find student2 in report
        $student2Report = collect($report['students'])->firstWhere('student_id', 'STU002');
        $this->assertNotNull($student2Report);
        $this->assertEquals(3, $student2Report['present']);
        $this->assertEquals(2, $student2Report['absent']);
        $this->assertEquals(0, $student2Report['late']);
        $this->assertEquals(5, $student2Report['total_days']);
        $this->assertEquals(60.0, $student2Report['percentage']); // 3/5 * 100 = 60%

        // Assert summary calculations
        $this->assertEquals(2, $report['summary']['total_students']);
        $this->assertEquals(7, $report['summary']['total_present']); // 4 + 3
        $this->assertEquals(2, $report['summary']['total_absent']); // 0 + 2
        $this->assertEquals(1, $report['summary']['total_late']); // 1 + 0
        $this->assertEquals(70.0, $report['summary']['average_percentage']); // (80 + 60) / 2
    }
}
