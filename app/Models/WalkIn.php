<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalkIn extends Model
{
    public const SESSION_FEES = [
        'Regular Walk-In' => 65,
        'Student Walk-In' => 50,
    ];

    protected $fillable = [
        'receipt_no',
        'first_name',
        'last_name',
        'session_type',
        'amount',
        'payment_method',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
    ];
}
