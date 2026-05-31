<?php

namespace App\Traits;

use App\Models\Family;
use App\Models\Scopes\FamilyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToFamily
{
    protected static function bootBelongsToFamily(): void
    {
        static::addGlobalScope(new FamilyScope);

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->family_id ??= auth()->user()->family_id;
            }
        });
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
