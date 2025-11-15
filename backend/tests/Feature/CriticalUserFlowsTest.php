<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CriticalUserFlowsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'teacher@school.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Test Flow 1: Login → Dashboard (see stats)
     * Requirements: 11.2, 11.3, 7.3, 10.1, 10.4
     */
    public function test_user_can_login_and_view_dashboard_statistics(): void
    {
        // Step 1: Login
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'teacher@school.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email']
                ]
            ]);

        $token = $loginResponse->json('data.token');

        // Create some test data for dashboard
        $students = Student::factory()->count(5)->create(['class' => '10A']);
        
        foreach ($students as $index => $student) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'date' => Carbon::today(),
                'status' => $index < 3 ? 'Present' : ($index < 4 ? 'Absent' : 'Late'),
                'recorded_by' => $this->user->id,
            ]);
        }

        // Step 2: Get today's statistics for dashboard
        $statsResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/attendance/today');

        $statsResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_students',
                    'present',
                    'absent',
                    'late',
                    'percentage'
                ]
            ]);

        $stats = $statsResponse->json('data');
        $this->assertEquals(5, $stats['total_students']);
        $this->assertEquals(3, $stats['present']);
        $this->assertEquals(1, $stats['absent']);
        $this->assertEquals(1, $stats['late']);
        $this->assertEquals(60.0, $stats['percentage']);
    }

    /**
     * Test Flow 2: Students list with search/filter
     * Requirements: 2.3, 8.1, 8.2, 8.3, 8.4
     */
    public function test_user_can_view_and_filter_students_list(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        // Create test students
        Student::factory()->create([
            'name' => 'John Doe',
            'student_id' => 'STU001',
            'class' => '10A',
            'section' => 'A'
        ]);
        
        Student::factory()->create([
            'name' => 'Jane Smith',
            'student_id' => 'STU002',
            'class' => '10B',
            'section' => 'B'
        ]);
        
        Student::factory()->create([
            'name' => 'Bob Johnson',
            'student_id' => 'STU003',
            'class' => '10A',
            'section' => 'A'
        ]);

        // Test 1: Get all students with pagination
        $allStudentsResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/students?page=1');

        $allStudentsResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'student_id', 'class', 'section']
                ],
                'meta' => ['current_page', 'total']
            ]);

        $this->assertCount(3, $allStudentsResponse->json('data'));

        // Test 2: Filter by class
        $classFilterResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/students?class=10A');

        $classFilterResponse->assertStatus(200);
        $this->assertCount(2, $classFilterResponse->json('data'));

        // Test 3: Search by name
        $searchResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/students?search=John');

        $searchResponse->assertStatus(200);
        $searchData = $searchResponse->json('data');
        $this->assertCount(2, $searchData); // John Doe and Bob Johnson
        
        // Test 4: Search by student_id
        $searchByIdResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/students?search=STU001');

        $searchByIdResponse->assertStatus(200);
        $this->assertCount(1, $searchByIdResponse->json('data'));
        $this->assertEquals('John Doe', $searchByIdResponse->json('data.0.name'));
    }

    /**
     * Test Flow 3: Record attendance for a class
     * Requirements: 2.4, 3.1, 5.2, 9.1, 9.2, 9.3, 9.5
     */
    public function test_user_can_record_attendance_for_class(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        // Create students in a class
        $students = Student::factory()->count(4)->create([
            'class' => '10A',
            'section' => 'A'
        ]);

        // Record bulk attendance
        $attendanceData = [
            'date' => Carbon::today()->format('Y-m-d'),
            'class' => '10A',
            'section' => 'A',
            'attendances' => [
                [
                    'student_id' => $students[0]->id,
                    'status' => 'Present',
                    'note' => ''
                ],
                [
                    'student_id' => $students[1]->id,
                    'status' => 'Present',
                    'note' => ''
                ],
                [
                    'student_id' => $students[2]->id,
                    'status' => 'Absent',
                    'note' => 'Sick leave'
                ],
                [
                    'student_id' => $students[3]->id,
                    'status' => 'Late',
                    'note' => 'Traffic'
                ],
            ]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/attendance/bulk', $attendanceData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'recorded_count',
                    'date',
                    'statistics' => ['present', 'absent', 'late', 'percentage']
                ]
            ]);

        $responseData = $response->json('data');
        $this->assertEquals(4, $responseData['recorded_count']);
        $this->assertEquals(2, $responseData['statistics']['present']);
        $this->assertEquals(1, $responseData['statistics']['absent']);
        $this->assertEquals(1, $responseData['statistics']['late']);
        $this->assertEquals(50.0, $responseData['statistics']['percentage']);

        // Verify attendance was recorded in database
        $this->assertDatabaseCount('attendances', 4);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[0]->id,
            'status' => 'Present',
            'date' => Carbon::today()->format('Y-m-d')
        ]);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[2]->id,
            'status' => 'Absent',
            'note' => 'Sick leave'
        ]);
    }

    /**
     * Test Flow 4: View monthly report
     * Requirements: 5.5, 6.1, 14.3
     */
    public function test_user_can_view_monthly_attendance_report(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        // Create students
        $students = Student::factory()->count(3)->create(['class' => '10A']);

        // Create attendance records for the month
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        foreach ($students as $student) {
            // Create 20 days of attendance
            for ($day = 1; $day <= 20; $day++) {
                $date = Carbon::create($currentYear, $currentMonth, $day);
                if ($date->isWeekday() && $date->lte(Carbon::now())) {
                    Attendance::factory()->create([
                        'student_id' => $student->id,
                        'date' => $date,
                        'status' => $day <= 18 ? 'Present' : 'Absent',
                        'recorded_by' => $this->user->id,
                    ]);
                }
            }
        }

        // Get monthly report
        $reportResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/attendance/monthly-report?month={$currentMonth}&year={$currentYear}&class=10A");

        $reportResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'month',
                    'year',
                    'class',
                    'students' => [
                        '*' => [
                            'id',
                            'name',
                            'student_id',
                            'total_days',
                            'present',
                            'absent',
                            'late',
                            'percentage'
                        ]
                    ],
                    'summary' => [
                        'total_students',
                        'average_percentage'
                    ]
                ]
            ]);

        $reportData = $reportResponse->json('data');
        $this->assertEquals($currentMonth, $reportData['month']);
        $this->assertEquals($currentYear, $reportData['year']);
        $this->assertEquals('10A', $reportData['class']);
        $this->assertCount(3, $reportData['students']);
        $this->assertEquals(3, $reportData['summary']['total_students']);
    }

    /**
     * Test Flow 5: Run artisan command
     * Requirements: 11.2, 11.3, 11.4
     */
    public function test_artisan_command_generates_report(): void
    {
        // Create test data
        $students = Student::factory()->count(2)->create(['class' => 'Class 10A']);
        
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        foreach ($students as $student) {
            for ($day = 1; $day <= 15; $day++) {
                $date = Carbon::create($currentYear, $currentMonth, $day);
                if ($date->isWeekday() && $date->lte(Carbon::now())) {
                    Attendance::factory()->create([
                        'student_id' => $student->id,
                        'date' => $date,
                        'status' => 'Present',
                        'recorded_by' => $this->user->id,
                    ]);
                }
            }
        }

        // Run the artisan command
        $this->artisan('attendance:generate-report', [
            'month' => $currentMonth,
            'class' => 'Class 10A',
            '--year' => $currentYear
        ])
        ->expectsOutput("Generating attendance report for Class 10A - Month: {$currentMonth}/{$currentYear}")
        ->assertExitCode(0);

        // Verify report file was created
        $reportFiles = \Storage::disk('local')->files('reports');
        $this->assertNotEmpty($reportFiles);
    }

    /**
     * Test complete end-to-end flow: Login → View Students → View Report
     * Requirements: 2.3, 2.4, 3.1, 5.2, 5.5, 6.1, 7.3, 11.2, 11.3, 11.4, 14.3
     */
    public function test_complete_end_to_end_user_flow(): void
    {
        // Step 1: Login
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'teacher@school.com',
            'password' => 'password',
        ]);
        
        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');
        $this->assertNotEmpty($token);

        // Step 2: View students list with search and filter
        $students = Student::factory()->count(5)->create(['class' => '10A']);
        Student::factory()->count(2)->create(['class' => '10B']);
        
        // Test viewing all students
        $allStudentsResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/students');
        
        $allStudentsResponse->assertStatus(200);
        $this->assertGreaterThanOrEqual(7, count($allStudentsResponse->json('data')));

        // Test filtering by class
        $filteredResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/students?class=10A');
        
        $filteredResponse->assertStatus(200);
        $this->assertCount(5, $filteredResponse->json('data'));

        // Step 3: Create attendance records manually for testing
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        foreach ($students as $student) {
            Attendance::factory()->create([
                'student_id' => $student->id,
                'date' => Carbon::today(),
                'status' => 'Present',
                'recorded_by' => $this->user->id,
            ]);
        }

        // Step 4: View monthly report
        $reportResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/attendance/monthly-report?month={$currentMonth}&year={$currentYear}&class=10A");
        
        $reportResponse->assertStatus(200);
        $reportData = $reportResponse->json('data');
        $this->assertEquals('10A', $reportData['class']);
        $this->assertCount(5, $reportData['students']);
        $this->assertEquals(5, $reportData['summary']['total_students']);
    }

}
