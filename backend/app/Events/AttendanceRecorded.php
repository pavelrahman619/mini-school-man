<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AttendanceRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The attendance records that were recorded.
     *
     * @var Collection
     */
    public Collection $attendances;

    /**
     * The user who recorded the attendance.
     *
     * @var User
     */
    public User $recordedBy;

    /**
     * Create a new event instance.
     *
     * @param Collection $attendances
     * @param User $recordedBy
     */
    public function __construct(Collection $attendances, User $recordedBy)
    {
        $this->attendances = $attendances;
        $this->recordedBy = $recordedBy;
    }
}
