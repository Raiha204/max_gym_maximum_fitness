<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
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
        return $diff > 0 ? 'Active' : 'Expired';
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->membership_status === 'Expired') {
            return 'expired';
        }
        if ($this->days_remaining <= 7) {
            return 'expiring';
        }
        return 'active';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }
        if (str_starts_with($this->photo_path, 'http') || str_starts_with($this->photo_path, 'data:')) {
            return $this->photo_path;
        }
        return asset('storage/' . $this->photo_path);
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
