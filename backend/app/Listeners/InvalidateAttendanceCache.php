<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class InvalidateAttendanceCache
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param AttendanceRecorded $event
     * @return void
     */
    public function handle(AttendanceRecorded $event): void
    {
        try {
            // Get the date from the first attendance record
            $date = $event->attendances->first()?->date ?? now()->format('Y-m-d');
            
            // Invalidate today's statistics cache
            $todayCacheKey = 'attendance:stats:today:' . $date;
            Redis::del($todayCacheKey);
            
            // Invalidate all attendance-related caches
            $pattern = 'attendance:stats:*';
            $keys = Redis::keys($pattern);
            
            if (!empty($keys)) {
                Redis::del($keys);
            }
            
            Log::info('Attendance cache invalidated after recording', [
                'date' => $date,
                'records_count' => $event->attendances->count(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to invalidate attendance cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
