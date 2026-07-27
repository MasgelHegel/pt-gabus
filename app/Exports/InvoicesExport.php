<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function query()
    {
        if ($this->query) {
            return $this->query;
        }

        return Invoice::with(['customer', 'latestPayment', 'items.product'])
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'No. Invoice',
            'Customer',
            'Detail Barang',
            'Tgl Invoice',
            'Jatuh Tempo',
            'Total Tagihan',
            'Gasback',
            'Sisa Piutang',
            'Status',
            'Tgl Bayar',
            'No. Pembayaran',
        ];
    }

    public function map($invoice): array
    {
        $itemsStr = $invoice->items->map(fn ($item) => "{$item->product?->name} ({$item->quantity} {$item->product?->unit})")->join(', ');
        $gasback = (int) ($invoice->gasback ?? 0);

        return [
            $invoice->invoice_number,
            $invoice->customer?->name ?? '-',
            $itemsStr,
            $invoice->invoice_date->format('d/m/Y'),
            $invoice->due_date->format('d/m/Y'),
            (float) $invoice->total_amount,
            $gasback,
            $invoice->status->value === 'paid' ? 0 : (float) $invoice->total_amount,
            $invoice->status->label(),
            $invoice->latestPayment?->payment_date?->format('d/m/Y') ?? '-',
            $invoice->latestPayment?->payment_number ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => '#,##0',
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
