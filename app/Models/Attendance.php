<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'training_schedule_id',
        'duty_assignment_id',
        'date',
        'time',
        'status', // Hadir, Izin, Sakit, Alpa
        'notes',
        'session_rate',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'session_rate' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingSchedule(): BelongsTo
    {
        return $this->belongsTo(TrainingSchedule::class);
    }

    public function dutyAssignment(): BelongsTo
    {
        return $this->belongsTo(DutyAssignment::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AttendancePhoto::class);
    }

    public function photo(): HasOne
    {
        return $this->hasOne(AttendancePhoto::class)->latestOfMany();
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $photo = $this->photo;

        return $photo ? asset($photo->photo_path) : null;
    }
}
