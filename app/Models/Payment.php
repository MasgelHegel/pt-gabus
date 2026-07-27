<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use Auditable, HasFactory;

    protected array $auditExclude = ['proof_file'];

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'customer_id',
        'account_id',
        'amount',
        'payment_date',
        'proof_file',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'status'       => PaymentStatus::class,
        'payment_date' => 'date',
        'verified_at'  => 'datetime',
        'amount'       => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getProofUrlAttribute(): ?string
    {
        if ($this->proof_file) {
            return asset('storage/' . $this->proof_file);
        }
        return null;
    }

    protected static function booted(): void
    {
        static::deleting(function (Payment $payment) {
            if ($payment->status === \App\Enums\PaymentStatus::Verified) {
                \App\Models\Customer::where('id', $payment->customer_id)
                    ->increment('piutang_balance', (float) $payment->amount);
            }

            \App\Models\JournalEntry::where('reference', $payment->payment_number)->get()->each(function ($journal) {
                $journal->delete();
            });
        });
    }
}
