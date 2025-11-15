<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use App\Events\AttendanceRecorded;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    /**
     * Record bulk attendance with DB transactions and duplicate prevention.
     *
     * @param array $attendanceData Array of attendance records
     * @param User $user The user recording the attendance
     * @return Collection Collection of recorded attendance records
     * @throws \Exception
     */
    public function recordBulkAttendance(array $attendanceData, User $user): Collection
    {
        DB::beginTransaction();
        
        try {
            $recorded = collect();
            
            foreach ($attendanceData as $data) {
                // Check for duplicate attendance
                $existing = Attendance::where('student_id', $data['student_id'])
                    ->whereDate('date', $data['date'])
                    ->first();
                
                if ($existing) {
                    // Skip duplicate or update existing
                    $existing->update([
                        'status' => $data['status'],
                        'note' => $data['note'] ?? null,
                        'recorded_by' => $user->id,
                    ]);
                    $recorded->push($existing);
                } else {
                    // Create new attendance record
                    $attendance = Attendance::create([
                        'student_id' => $data['student_id'],
                        'date' => $data['date'],
                        'status' => $data['status'],
                        'note' => $data['note'] ?? null,
                        'recorded_by' => $user->id,
                    ]);
                    $recorded->push($attendance);
                }
            }
            
            DB::commit();
            
            // Dispatch event after successful recording
            event(new AttendanceRecorded($recorded, $user));
            
            return $recorded;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk attendance recording failed', [
                'error' => $e->getMessage(),
                'user' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate monthly attendance report with eager loading and percentage calculations.
     *
     * @param int $month Month (1-12)
     * @param int $year Year
     * @param string $class Class name
     * @return array Report data with student statistics
     */
    public function generateMonthlyReport(int $month, int $year, string $class): array
    {
        // Get all students in the class
        $students = Student::byClass($class)
            ->with(['attendances' => function ($query) use ($month, $year) {
                $query->forMonth($month, $year);
            }])
            ->get();
        
        // Calculate total working days in the month
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $totalDays = Attendance::forMonth($month, $year)
            ->distinct('date')
            ->count('date');
        
        // If no attendance records exist, calculate business days
        if ($totalDays === 0) {
            $totalDays = $this->calculateBusinessDays($startDate, $endDate);
        }
        
        $studentReports = [];
        $totalPresent = 0;
        $totalAbsent = 0;
        $totalLate = 0;
        
        foreach ($students as $student) {
            $present = $student->attendances->where('status', 'Present')->count();
            $absent = $student->attendances->where('status', 'Absent')->count();
            $late = $student->attendances->where('status', 'Late')->count();
            
            $percentage = $totalDays > 0 
                ? round(($present / $totalDays) * 100, 2) 
                : 0;
            
            $studentReports[] = [
                'id' => $student->id,
                'name' => $student->name,
                'student_id' => $student->student_id,
                'total_days' => $totalDays,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'percentage' => $percentage,
            ];
            
            $totalPresent += $present;
            $totalAbsent += $absent;
            $totalLate += $late;
        }
        
        $averagePercentage = count($studentReports) > 0
            ? round(collect($studentReports)->avg('percentage'), 2)
            : 0;
        
        return [
            'month' => $month,
            'year' => $year,
            'class' => $class,
            'students' => $studentReports,
            'summary' => [
                'total_students' => count($studentReports),
                'average_percentage' => $averagePercentage,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_late' => $totalLate,
            ],
        ];
    }

    /**
     * Get today's attendance statistics with Redis caching.
     *
     * @return array Today's statistics
     */
    public function getTodayStatistics(): array
    {
        $cacheKey = 'attendance:stats:today:' . now()->format('Y-m-d');
        
        try {
            // Try to get from Redis cache
            $cached = Redis::get($cacheKey);
            
            if ($cached !== null) {
                return json_decode($cached, true);
            }
        } catch (\Exception $e) {
            Log::warning('Redis cache retrieval failed, falling back to database', [
                'error' => $e->getMessage(),
            ]);
        }
        
        // Fallback to database
        $statistics = $this->calculateTodayStatistics();
        
        try {
            // Cache for 3600 seconds (1 hour)
            Redis::setex($cacheKey, 3600, json_encode($statistics));
        } catch (\Exception $e) {
            Log::warning('Redis cache storage failed', [
                'error' => $e->getMessage(),
            ]);
        }
        
        return $statistics;
    }
    
    /**
     * Calculate today's statistics from database.
     *
     * @return array Statistics data
     */
    protected function calculateTodayStatistics(): array
    {
        $today = now()->format('Y-m-d');
        
        $attendances = Attendance::forDate($today)->get();
        
        $present = $attendances->where('status', 'Present')->count();
        $absent = $attendances->where('status', 'Absent')->count();
        $late = $attendances->where('status', 'Late')->count();
        $total = $attendances->count();
        
        $percentage = $total > 0 
            ? round(($present / $total) * 100, 2) 
            : 0;
        
        return [
            'date' => $today,
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'percentage' => $percentage,
        ];
    }

    /**
     * Invalidate all attendance statistics caches.
     *
     * @return void
     */
    public function invalidateStatisticsCache(): void
    {
        try {
            $today = now()->format('Y-m-d');
            $cacheKey = 'attendance:stats:today:' . $today;
            
            Redis::del($cacheKey);
            
            // Also invalidate class-specific caches if they exist
            $pattern = 'attendance:stats:*';
            $keys = Redis::keys($pattern);
            
            if (!empty($keys)) {
                Redis::del($keys);
            }
            
            Log::info('Attendance statistics cache invalidated');
        } catch (\Exception $e) {
            Log::warning('Failed to invalidate cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Calculate business days between two dates (excluding weekends).
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return int Number of business days
     */
    protected function calculateBusinessDays(Carbon $startDate, Carbon $endDate): int
    {
        $days = 0;
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            // Count only weekdays (Monday to Friday)
            if ($current->isWeekday()) {
                $days++;
            }
            $current->addDay();
        }
        
        return $days;
    }
}
