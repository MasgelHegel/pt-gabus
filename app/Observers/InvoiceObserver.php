<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Journal\CreateJournalEntryAction;
use App\Enums\InvoiceStatus;
use App\Models\Account;
use App\Models\Invoice;

class InvoiceObserver
{
    public function __construct(
        private readonly CreateJournalEntryAction $journalAction,
    ) {}

    /**
     * Auto-create journal on invoice creation:
     * Dr  Piutang Usaha   (+)
     * Cr  Pendapatan      (+)
     */
    public function created(Invoice $invoice): void
    {
        $receivable = Account::firstWhere('code', '1120');
        $revenue    = Account::firstWhere('code', '4100');

        if (! $receivable || ! $revenue) {
            return;
        }

        ($this->journalAction)(
            description: "Invoice #{$invoice->invoice_number} - Penjualan",
            lines: [
                ['account_id' => $receivable->id, 'debit' => (float) $invoice->total_amount, 'credit' => 0],
                ['account_id' => $revenue->id,    'debit' => 0, 'credit' => (float) $invoice->total_amount],
            ],
            reference: $invoice->invoice_number,
        );
    }
}
