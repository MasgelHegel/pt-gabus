<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Journal\CreateJournalEntryAction;
use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\Payment;

class PaymentObserver
{
    public function __construct(
        private readonly CreateJournalEntryAction $journalAction,
    ) {}

    /**
     * Auto-create journal on payment verification:
     * Dr  Kas / Bank      (+)
     * Cr  Piutang Usaha   (-)
     */
    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status')) {
            return;
        }

        if ($payment->status !== PaymentStatus::Verified) {
            return;
        }

        $cashAccount      = Account::find($payment->account_id) ?? Account::firstWhere('is_cash_bank', true);
        $receivableAccount = Account::firstWhere('code', '1120');

        if (! $cashAccount || ! $receivableAccount) {
            return;
        }

        ($this->journalAction)(
            description: "Pembayaran #{$payment->payment_number} - Penerimaan Kas",
            lines: [
                ['account_id' => $cashAccount->id,       'debit' => (float) $payment->amount, 'credit' => 0],
                ['account_id' => $receivableAccount->id, 'debit' => 0, 'credit' => (float) $payment->amount],
            ],
            reference: $payment->payment_number,
        );
    }
}
