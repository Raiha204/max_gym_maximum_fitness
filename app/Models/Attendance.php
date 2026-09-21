<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_id',
        'attendance_date',
        'check_in',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }
}
