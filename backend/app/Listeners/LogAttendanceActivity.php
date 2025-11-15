<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogAttendanceActivity
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
        $attendanceCount = $event->attendances->count();
        $date = $event->attendances->first()?->date ?? now()->format('Y-m-d');
        
        // Calculate statistics
        $present = $event->attendances->where('status', 'Present')->count();
        $absent = $event->attendances->where('status', 'Absent')->count();
        $late = $event->attendances->where('status', 'Late')->count();
        
        // Log attendance activity
        Log::channel('daily')->info('Attendance recorded', [
            'recorded_by' => $event->recordedBy->name,
            'user_id' => $event->recordedBy->id,
            'date' => $date,
            'total_records' => $attendanceCount,
            'statistics' => [
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
            ],
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
