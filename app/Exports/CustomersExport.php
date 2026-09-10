<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize
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

        return Customer::with('user')->orderBy('name', 'asc');
    }

    public function headings(): array
    {
        return [
            'Kode Customer',
            'Nama Customer',
            'Nama Perusahaan',
            'Email',
            'No. Telepon',
            'Alamat',
            'Batas Kredit (Rp)',
            'Saldo Piutang (Rp)',
            'TOP (Hari)',
            'Status Akun Portal',
            'Email Akun Portal',
            'Tanggal Terdaftar',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->code,
            $customer->name,
            $customer->company_name ?? '-',
            $customer->email ?? '-',
            $customer->phone ?? '-',
            $customer->address ?? '-',
            (float) ($customer->credit_limit ?? 0),
            (float) ($customer->piutang_balance ?? 0),
            (int) ($customer->due_period_days ?? 30),
            $customer->user_id ? 'Aktif' : 'Belum Ada Akun',
            $customer->user?->email ?? '-',
            $customer->created_at ? $customer->created_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
