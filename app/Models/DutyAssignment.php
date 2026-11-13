<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'duty_schedule_id',
        'user_id',
        'role_task',
        'training_materials',
        'status',
    ];

    public function dutySchedule(): BelongsTo
    {
        return $this->belongsTo(DutySchedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

