<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            if (! $member->member_number) {
                // A temporary unique value satisfies the database constraint;
                // the created hook replaces it with the sequential member ID.
                $member->member_number = 'MAX-TMP-'.Str::ulid();
            }
        });

        static::created(function (Member $member) {
            if (str_starts_with($member->member_number, 'MAX-TMP-')) {
                $member->forceFill([
                    'member_number' => 'MAX-'.str_pad((string) $member->getKey(), 8, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
