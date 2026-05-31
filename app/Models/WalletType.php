<?php

namespace App\Models;

use App\Traits\BelongsToFamily;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletType extends Model
{
    use BelongsToFamily, HasFactory;

    protected $fillable = [
        'family_id',
        'name',
        'icon',
    ];
}
