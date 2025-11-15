<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateAttendanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-report {month} {class} {--year=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly attendance report for a specific class';

    protected AttendanceService $attendanceService;

    /**
     * Create a new command instance.
     */
    public function __construct(AttendanceService $attendanceService)
    {
        parent::__construct();
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $month = $this->argument('month');
        $class = $this->argument('class');
        $year = $this->option('year') ?? now()->year;

        // Validate month
        if (!is_numeric($month) || $month < 1 || $month > 12) {
            $this->error('Invalid month. Please provide a month between 1 and 12.');
            $this->line('Usage: php artisan attendance:generate-report {month} {class} {--year=}');
            return Command::FAILURE;
        }

        // Validate year
        if (!is_numeric($year) || $year < 2000 || $year > 2100) {
            $this->error('Invalid year. Please provide a valid year.');
            return Command::FAILURE;
        }

        $this->info("Generating attendance report for {$class} - Month: {$month}, Year: {$year}");
        $this->newLine();

        try {
            // Generate report using AttendanceService
            $report = $this->attendanceService->generateMonthlyReport((int)$month, (int)$year, $class);

            if (empty($report['students'])) {
                $this->warn("No students found for class: {$class}");
                return Command::SUCCESS;
            }

            // Display formatted table to console
            $this->displayReport($report);

            // Save report to storage
            $this->saveReport($report, $month, $year, $class);

            $this->newLine();
            $this->info('Report generated successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate report: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Display the report as a formatted table in the console.
     *
     * @param array $report
     * @return void
     */
    protected function displayReport(array $report): void
    {
        $this->line("Attendance Report - {$report['class']} (Month: {$report['month']}/{$report['year']})");
        $this->line(str_repeat('=', 100));
        $this->newLine();

        // Student details table
        $headers = ['Student ID', 'Name', 'Total Days', 'Present', 'Absent', 'Late', 'Percentage'];
        $rows = [];

        foreach ($report['students'] as $student) {
            $rows[] = [
                $student['student_id'],
                $student['name'],
                $student['total_days'],
                $student['present'],
                $student['absent'],
                $student['late'],
                $student['percentage'] . '%',
            ];
        }

        $this->table($headers, $rows);

        // Summary statistics
        $this->newLine();
        $this->line('Summary Statistics:');
        $this->line(str_repeat('-', 100));
        $this->line("Total Students: {$report['summary']['total_students']}");
        $this->line("Average Attendance: {$report['summary']['average_percentage']}%");
        $this->line("Total Present: {$report['summary']['total_present']}");
        $this->line("Total Absent: {$report['summary']['total_absent']}");
        $this->line("Total Late: {$report['summary']['total_late']}");
    }

    /**
     * Save the report to storage as a text file.
     *
     * @param array $report
     * @param int $month
     * @param int $year
     * @param string $class
     * @return void
     */
    protected function saveReport(array $report, int $month, int $year, string $class): void
    {
        // Sanitize class name for filename
        $sanitizedClass = preg_replace('/[^A-Za-z0-9\-]/', '_', $class);
        $filename = "attendance-{$sanitizedClass}-{$month}-{$year}.txt";

        // Build report content
        $content = $this->buildReportContent($report);

        // Ensure reports directory exists
        if (!Storage::exists('reports')) {
            Storage::makeDirectory('reports');
        }

        // Save to storage/reports/
        Storage::put("reports/{$filename}", $content);

        $this->newLine();
        $this->info("Report saved to: storage/app/reports/{$filename}");
    }

    /**
     * Build the report content as a formatted string.
     *
     * @param array $report
     * @return string
     */
    protected function buildReportContent(array $report): string
    {
        $content = "ATTENDANCE REPORT\n";
        $content .= str_repeat('=', 100) . "\n";
        $content .= "Class: {$report['class']}\n";
        $content .= "Month: {$report['month']}/{$report['year']}\n";
        $content .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= str_repeat('=', 100) . "\n\n";

        // Student details
        $content .= sprintf(
            "%-15s %-30s %-12s %-10s %-10s %-10s %-12s\n",
            'Student ID',
            'Name',
            'Total Days',
            'Present',
            'Absent',
            'Late',
            'Percentage'
        );
        $content .= str_repeat('-', 100) . "\n";

        foreach ($report['students'] as $student) {
            $content .= sprintf(
                "%-15s %-30s %-12d %-10d %-10d %-10d %-12s\n",
                $student['student_id'],
                substr($student['name'], 0, 30),
                $student['total_days'],
                $student['present'],
                $student['absent'],
                $student['late'],
                $student['percentage'] . '%'
            );
        }

        // Summary
        $content .= "\n" . str_repeat('=', 100) . "\n";
        $content .= "SUMMARY STATISTICS\n";
        $content .= str_repeat('-', 100) . "\n";
        $content .= "Total Students: {$report['summary']['total_students']}\n";
        $content .= "Average Attendance: {$report['summary']['average_percentage']}%\n";
        $content .= "Total Present: {$report['summary']['total_present']}\n";
        $content .= "Total Absent: {$report['summary']['total_absent']}\n";
        $content .= "Total Late: {$report['summary']['total_late']}\n";

        return $content;
    }
}
