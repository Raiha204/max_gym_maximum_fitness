<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'code',
        'name',
        'category',
        'status',
        'last_inspected',
        'notes',
    ];

    protected $casts = [
        'last_inspected' => 'date',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
