<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Get or create standard Chart of Accounts (COA)
     */
    public function getOrCreateAccount(string $code, string $name, string $type, bool $isCashBank = false): Account
    {
        return Account::firstOrCreate(
            ['code' => $code],
            [
                'name'         => $name,
                'type'         => $type,
                'balance'      => 0,
                'is_cash_bank' => $isCashBank,
            ]
        );
    }

    /**
     * Post Automatic Journal Entry when an Invoice is issued (Piutang Bertambah, Penjualan Bertambah)
     * Debit: Piutang Usaha
     * Credit: Penjualan
     */
    public function postInvoiceJournal(Invoice $invoice): JournalEntry
    {
        return DB::transaction(function () use ($invoice) {
            $arAccount = $this->getOrCreateAccount('1-1020', 'Piutang Usaha', 'asset');
            $salesAccount = $this->getOrCreateAccount('4-1000', 'Pendapatan Penjualan', 'revenue');

            $entry = JournalEntry::create([
                'entry_number' => 'JRN-INV-' . time() . '-' . rand(100, 999),
                'entry_date'   => now()->toDateString(),
                'reference'    => $invoice->invoice_number,
                'description'  => 'Jurnal Penjualan Invoice #' . $invoice->invoice_number,
                'created_by'   => auth()->id(),
            ]);

            // Debit Piutang Usaha (Aset +)
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $arAccount->id,
                'debit'            => $invoice->total_amount,
                'credit'           => 0,
            ]);
            $arAccount->increment('balance', $invoice->total_amount);

            // Credit Penjualan (Revenue +)
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $salesAccount->id,
                'debit'            => 0,
                'credit'           => $invoice->total_amount,
            ]);
            $salesAccount->increment('balance', $invoice->total_amount);

            return $entry;
        });
    }

    /**
     * Post Automatic Journal Entry when Payment is Verified (Kas Bertambah, Piutang Berkurang)
     * Debit: Kas & Bank
     * Credit: Piutang Usaha
     */
    public function postPaymentJournal(Payment $payment): JournalEntry
    {
        return DB::transaction(function () use ($payment) {
            $cashAccount = $payment->account ?? $this->getOrCreateAccount('1-1010', 'Kas & Bank', 'asset', true);
            $arAccount = $this->getOrCreateAccount('1-1020', 'Piutang Usaha', 'asset');

            $entry = JournalEntry::create([
                'entry_number' => 'JRN-PAY-' . time() . '-' . rand(100, 999),
                'entry_date'   => now()->toDateString(),
                'reference'    => $payment->payment_number,
                'description'  => 'Jurnal Pembayaran Invoice #' . $payment->invoice->invoice_number,
                'created_by'   => auth()->id(),
            ]);

            // Debit Kas & Bank (Kas Bertambah)
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $cashAccount->id,
                'debit'            => $payment->amount,
                'credit'           => 0,
            ]);
            $cashAccount->increment('balance', $payment->amount);

            // Credit Piutang Usaha (Piutang Berkurang)
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $arAccount->id,
                'debit'            => 0,
                'credit'           => $payment->amount,
            ]);
            $arAccount->decrement('balance', $payment->amount);

            return $entry;
        });
    }
}
