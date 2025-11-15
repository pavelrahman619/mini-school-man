<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'student_id',
        'class',
        'section',
        'photo',
    ];

    /**
     * Get all attendances for the student.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Scope a query to filter students by class.
     */
    public function scopeByClass($query, string $class)
    {
        return $query->where('class', $class);
    }

    /**
     * Scope a query to filter students by section.
     */
    public function scopeBySection($query, string $section)
    {
        return $query->where('section', $section);
    }
}
