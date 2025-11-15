<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use App\Http\Resources\AttendanceResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Record bulk attendance for multiple students.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => ['required', Rule::in(['Present', 'Absent', 'Late'])],
            'attendances.*.note' => 'nullable|string|max:500',
        ]);

        try {
            $user = $request->user();
            
            // Prepare attendance data with the date
            $attendanceData = collect($validated['attendances'])->map(function ($attendance) use ($validated) {
                return [
                    'student_id' => $attendance['student_id'],
                    'date' => $validated['date'],
                    'status' => $attendance['status'],
                    'note' => $attendance['note'] ?? null,
                ];
            })->toArray();

            $recorded = $this->attendanceService->recordBulkAttendance($attendanceData, $user);

            // Calculate statistics
            $present = $recorded->where('status', 'Present')->count();
            $absent = $recorded->where('status', 'Absent')->count();
            $late = $recorded->where('status', 'Late')->count();
            $total = $recorded->count();
            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'message' => "Attendance recorded for {$total} student(s)",
                'data' => [
                    'recorded_count' => $total,
                    'date' => $validated['date'],
                    'statistics' => [
                        'present' => $present,
                        'absent' => $absent,
                        'late' => $late,
                        'percentage' => $percentage,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to record bulk attendance', [
                'error' => $e->getMessage(),
                'user' => $request->user()->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record attendance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate monthly attendance report.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'class' => 'required|string|max:50',
        ]);

        try {
            $report = $this->attendanceService->generateMonthlyReport(
                $validated['month'],
                $validated['year'],
                $validated['class']
            );

            return response()->json([
                'success' => true,
                'data' => $report,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to generate monthly report', [
                'error' => $e->getMessage(),
                'params' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate monthly report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's attendance statistics with caching.
     *
     * @return JsonResponse
     */
    public function todayStatistics(): JsonResponse
    {
        try {
            $statistics = $this->attendanceService->getTodayStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve today\'s statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
