<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
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
            Cache::forget($todayCacheKey);
            
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
