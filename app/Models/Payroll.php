<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'period_month',
        'period_year',
        'total_sessions_attended',
        'rate_per_session',
        'base_salary',
        'bonus_allowance',
        'deductions',
        'total_salary',
        'payment_status',
        'payment_method',
        'payment_date',
        'payment_notes',
        'approved_by',
        'approved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'total_sessions_attended' => 'integer',
            'rate_per_session' => 'decimal:2',
            'base_salary' => 'decimal:2',
            'bonus_allowance' => 'decimal:2',
            'deductions' => 'decimal:2',
            'total_salary' => 'decimal:2',
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'payment_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $months[$this->period_month] ?? (string) $this->period_month;
    }
}

