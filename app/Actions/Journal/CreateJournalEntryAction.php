<?php

declare(strict_types=1);

namespace App\Actions\Journal;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class CreateJournalEntryAction
{
    /**
     * @param array<int, array{account_id: int, debit: float, credit: float}> $lines
     */
    public function __invoke(
        string  $description,
        array   $lines,
        ?string $reference = null,
        ?int    $createdBy = null,
    ): JournalEntry {
        return DB::transaction(function () use ($description, $lines, $reference, $createdBy) {

            $entryNumber = 'JRN-' . now()->format('Ymd') . '-'
                . str_pad((string) (JournalEntry::count() + 1), 4, '0', STR_PAD_LEFT);

            /** @var JournalEntry $entry */
            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date'   => now()->toDateString(),
                'reference'    => $reference,
                'description'  => $description,
                'created_by'   => $createdBy ?? auth()->id(),
            ]);

            foreach ($lines as $line) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'debit'            => $line['debit']  ?? 0,
                    'credit'           => $line['credit'] ?? 0,
                ]);
            }

            return $entry->load('lines');
        });
    }
}
