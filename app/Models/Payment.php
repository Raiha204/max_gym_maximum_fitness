<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * Walk-in (single day-use) price per visitor type.
     */
    public const WALK_IN_PRICES = [
        'regular' => 65,
        'student' => 50,
    ];

    protected $fillable = [
        'membership_id',
        'visitor_type',
        'payment_date',
        'amount',
        'payment_method',
        'status',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * A friendly label for display: who/what this payment was for.
     */
    public function getLabelAttribute(): string
    {
        if ($this->membership) {
            return 'Membership — '.$this->membership->fullName();
        }

        return 'Walk-in ('.ucfirst($this->visitor_type).')';
    }
}
