<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
    ];

    protected $casts = [
        'debit'  => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    protected static function booted(): void
    {
        static::created(function (JournalLine $line) {
            $account = $line->account()->first();
            if ($account) {
                $debit = (float) $line->debit;
                $credit = (float) $line->credit;

                if ($account->type === \App\Enums\AccountType::Asset || $account->type === \App\Enums\AccountType::Expense) {
                    $account->increment('balance', $debit - $credit);
                } else {
                    $account->increment('balance', $credit - $debit);
                }
            }
        });
    }
}
