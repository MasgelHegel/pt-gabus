<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference',
        'description',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::deleting(function (JournalEntry $journalEntry) {
            foreach ($journalEntry->lines()->with('account')->get() as $line) {
                $account = $line->account;
                if ($account) {
                    if ($line->debit > 0) {
                        $account->decrement('balance', (float) $line->debit);
                    }
                    if ($line->credit > 0) {
                        if ($account->type === \App\Enums\AccountType::Asset || $account->type === \App\Enums\AccountType::Expense) {
                            $account->increment('balance', (float) $line->credit);
                        } else {
                            $account->decrement('balance', (float) $line->credit);
                        }
                    }
                }
            }
            $journalEntry->lines()->delete();
        });
    }
}
