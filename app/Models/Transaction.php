<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'account_id',
        'reference',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
