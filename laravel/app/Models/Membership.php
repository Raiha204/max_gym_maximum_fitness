<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    public const DAILY_CREDIT_DEDUCTION = 50;

    public const PLAN_RATES = [
        'Student Membership' => 650,
        'Regular Membership' => 750,
    ];

    protected $fillable = [
        'member_id',
        'full_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'photo_path',
        'plan_type',
        'duration_months',
        'monthly_rate',
        'total_amount',
        'remaining_amount',
        'daily_credit_enabled',
        'daily_credit_started_at',
        'payment_method',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_months' => 'integer',
        'monthly_rate' => 'float',
        'total_amount' => 'float',
        'remaining_amount' => 'float',
        'daily_credit_enabled' => 'boolean',
        'daily_credit_started_at' => 'datetime',
    ];

    public static function generateMemberId(): string
    {
        do {
            $id = (string) random_int(100000, 999999);
        } while (self::where('member_id', $id)->exists());

        return $id;
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }
        $diff = Carbon::today()->diffInDays($this->end_date->copy()->startOfDay(), false);
        return max(0, (int) $diff);
    }

    public function getMembershipStatusAttribute(): string
    {
        if (!$this->end_date) {
            return 'Expired';
        }
        $diff = Carbon::today()->diffInDays($this->end_date->copy()->startOfDay(), false);
        if ($diff <= 0) {
            return 'Expired';
        }

        if (!$this->daily_credit_enabled) {
            return 'Active';
        }

        return $this->available_credit > 0 ? 'Partial' : 'Expired';
    }

    public function getAvailableCreditAttribute(): float
    {
        if (!$this->daily_credit_enabled || !$this->daily_credit_started_at) {
            return 0.0;
        }

        $timezone = config('app.display_timezone', 'Asia/Manila');
        $startDate = $this->daily_credit_started_at->copy()->setTimezone($timezone)->startOfDay();
        $today = Carbon::now($timezone)->startOfDay();
        $payments = $this->relationLoaded('payments')
            ? $this->payments
                ->where('paid_at', '>=', $this->daily_credit_started_at)
                ->sortBy('paid_at')
            : $this->payments()
                ->where('paid_at', '>=', $this->daily_credit_started_at)
                ->orderBy('paid_at')
                ->get();
        $paymentsByDay = $payments->groupBy(
            fn (Payment $payment) => $payment->paid_at->copy()->setTimezone($timezone)->toDateString()
        );

        $credit = 0.0;
        $lastChargedDay = $startDate;
        foreach ($paymentsByDay as $date => $dayPayments) {
            $paymentDay = Carbon::parse($date, $timezone)->startOfDay();
            $daysToCharge = $lastChargedDay->diffInDays($paymentDay);
            $credit = max(0, $credit - (self::DAILY_CREDIT_DEDUCTION * $daysToCharge));
            $credit += $dayPayments->sum('amount');
            $lastChargedDay = $paymentDay;
        }

        $daysToCharge = $lastChargedDay->diffInDays($today);
        return round(max(0, $credit - (self::DAILY_CREDIT_DEDUCTION * $daysToCharge)), 2);
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->membership_status === 'Expired') {
            return 'expired';
        }
        if ($this->membership_status === 'Partial') {
            return 'partial';
        }
        if ($this->days_remaining <= 7) {
            return 'expiring';
        }
        return 'active';
    }

    /**
     * Names are stored as "Surname, First Name". Older records without a comma
     * fall back to treating the last word as the surname.
     */
    public function getSurnameAttribute(): string
    {
        $name = trim((string) $this->full_name);
        if (str_contains($name, ',')) {
            return trim(explode(',', $name, 2)[0]);
        }
        $parts = preg_split('/\s+/', $name) ?: [];
        return count($parts) > 1 ? (string) end($parts) : $name;
    }

    public function getGivenNameAttribute(): string
    {
        $name = trim((string) $this->full_name);
        if (str_contains($name, ',')) {
            return trim(explode(',', $name, 2)[1]);
        }
        $parts = preg_split('/\s+/', $name) ?: [];
        return count($parts) > 1 ? implode(' ', array_slice($parts, 0, -1)) : '';
    }

    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->given_name, 0, 1);
        $last = mb_substr($this->surname, 0, 1);
        return mb_strtoupper($first . $last) ?: '?';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }
        if (str_starts_with($this->photo_path, 'http') || str_starts_with($this->photo_path, 'data:')) {
            return $this->photo_path;
        }
        return route('memberships.photo', $this);
    }

    /**
     * Offline Member QR Profile Data (No token line below expiration date)
     */
    public function getOfflineQrTextAttribute(): string
    {
        $expires = optional($this->end_date)->format('F j, Y') ?? '—';
        return implode("\n", [
            'MAX GYM MEMBER',
            '',
            $this->full_name,
            '',
            "Member ID: {$this->member_id}",
            '',
            'Membership:',
            $this->plan_type,
            '',
            'Status:',
            $this->membership_status,
            '',
            'Expires:',
            $expires,
        ]);
    }
}
