<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'balance',
        'is_cash_bank',
    ];

    protected $casts = [
        'type'         => AccountType::class,
        'balance'      => 'decimal:2',
        'is_cash_bank' => 'boolean',
    ];

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}
