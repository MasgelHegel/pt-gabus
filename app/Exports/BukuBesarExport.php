<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\JournalLine;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BukuBesarExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return JournalLine::query()
            ->with(['journalEntry', 'account'])
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->select('journal_lines.*', 'journal_entries.entry_number', 'journal_entries.entry_date', 'journal_entries.description as entry_description', 'accounts.code as account_code', 'accounts.name as account_name')
            ->orderBy('accounts.code')
            ->orderBy('journal_entries.entry_date');
    }

    public function headings(): array
    {
        return [
            'Kode Akun',
            'Nama Akun',
            'Tanggal',
            'No. Jurnal',
            'Keterangan',
            'Debet',
            'Kredit',
        ];
    }

    public function map($row): array
    {
        $dateStr = '-';
        if ($row->entry_date) {
            $date = is_string($row->entry_date) ? \Carbon\Carbon::parse($row->entry_date) : $row->entry_date;
            $dateStr = $date->format('d/m/Y');
        }

        return [
            $row->account_code,
            $row->account_name,
            $dateStr,
            $row->entry_number,
            $row->entry_description ?? '-',
            (float) $row->debit,
            (float) $row->credit,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
