<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return Payment::with(['customer', 'invoice', 'verifiedBy'])
            ->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'No. Pembayaran',
            'No. Invoice',
            'Customer',
            'Nominal',
            'Tgl Bayar',
            'Status',
            'Diverifikasi Oleh',
            'Tgl Verifikasi',
            'Alasan Penolakan',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_number,
            $payment->invoice?->invoice_number ?? '-',
            $payment->customer?->name ?? '-',
            (float) $payment->amount,
            $payment->payment_date->format('d/m/Y'),
            $payment->status->label(),
            $payment->verifiedBy?->name ?? '-',
            $payment->verified_at?->format('d/m/Y H:i') ?? '-',
            $payment->rejection_reason ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
