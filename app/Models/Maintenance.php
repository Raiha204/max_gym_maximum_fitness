<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $table = 'maintenance';

    protected $fillable = [
        'equipment_id',
        'maintenance_date',
        'description',
        'cost',
        'status',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
