<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'customer_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'gasback',
        'status',
    ];

    protected $casts = [
        'status'       => InvoiceStatus::class,
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'subtotal'     => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'gasback'      => 'integer',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    protected static function booted(): void
    {
        static::deleting(function (Invoice $invoice) {
            if ($invoice->status !== \App\Enums\InvoiceStatus::Paid && $invoice->status !== \App\Enums\InvoiceStatus::Cancelled) {
                \App\Models\Customer::where('id', $invoice->customer_id)
                    ->decrement('piutang_balance', (float) $invoice->total_amount);
            }

            \App\Models\JournalEntry::where('reference', $invoice->invoice_number)->get()->each(function ($journal) {
                $journal->delete();
            });

            $invoice->payments()->get()->each(function ($payment) {
                $payment->delete();
            });

            $invoice->items()->delete();
        });
    }
}
