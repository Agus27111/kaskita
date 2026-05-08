<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'avatar',
        'invite_code',
    ];

    public function getInviteCodeAttribute($value)
    {
        if (!$value) {
            $code = strtoupper(\Illuminate\Support\Str::random(6));
            while (self::where('invite_code', $code)->exists()) {
                $code = strtoupper(\Illuminate\Support\Str::random(6));
            }
            $this->update(['invite_code' => $code]);
            return $code;
        }
        return $value;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}
