<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    /**
     * Monthly membership price per type. Kept in one place so the whole
     * system (registration form, controller) always agrees on the price.
     */
    public const PRICES = [
        'regular' => 750,
        'student' => 650,
    ];

    /**
     * Flat penalty deducted from what's already been paid if the member
     * misses their payment due date without completing the full amount.
     */
    public const LATE_PENALTY = 50;

    protected $fillable = [
        'first_name',
        'last_name',
        'member_type',
        'plan_name',
        'amount_due',
        'amount_paid',
        'payment_due_date',
        'penalty_applied',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'penalty_applied' => 'boolean',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getBalanceAttribute(): float
    {
        return round($this->amount_due - $this->amount_paid, 2);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    public function getIsPastDueAttribute(): bool
    {
        return ! $this->is_fully_paid
            && $this->payment_due_date
            && now()->toDateString() > $this->payment_due_date;
    }

    /**
     * If the member missed their payment due date without paying in full,
     * deduct the flat late penalty from what they've already paid. Only
     * ever applied once per membership (guarded by penalty_applied).
     */
    public function applyLatePenaltyIfNeeded(): void
    {
        if ($this->penalty_applied || ! $this->is_past_due) {
            return;
        }

        $this->amount_paid = max(0, $this->amount_paid - self::LATE_PENALTY);
        $this->penalty_applied = true;
        $this->save();
    }

    /**
     * Recalculate status based on payment and dates.
     * Membership only becomes active once fully paid, per gym's business rule.
     */
    public function refreshStatus(): void
    {
        if (! $this->is_fully_paid) {
            $this->status = 'pending';
        } elseif ($this->end_date && now()->toDateString() > $this->end_date) {
            $this->status = 'expired';
        } else {
            $this->status = 'active';
        }
        $this->save();
    }
}
