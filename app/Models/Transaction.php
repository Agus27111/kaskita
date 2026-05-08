<?php

namespace App\Models;

use App\Traits\BelongsToFamily;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, BelongsToFamily;

    protected $fillable = [
        'family_id',
        'wallet_id',
        'category_id',
        'user_id',
        'type',
        'amount',
        'note',
        'date',
        'receipt_photo',
        'transfer_to_wallet_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transferToWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'transfer_to_wallet_id');
    }
}
