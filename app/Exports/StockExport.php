<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return Product::with('category')
            ->latest('sku');
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Nama Produk',
            'Kategori',
            'Satuan',
            'Harga Beli',
            'Harga Jual',
            'Stok Saat Ini',
            'Nilai Aset (Stok * Harga Beli)',
        ];
    }

    public function map($row): array
    {
        $buyPrice = (float) $row->buy_price;
        $stock = (int) $row->stock;
        $assetValue = $stock * $buyPrice;

        return [
            $row->sku,
            $row->name,
            $row->category?->name ?? '-',
            $row->unit ?? 'pcs',
            $buyPrice,
            (float) $row->sell_price,
            $stock,
            $assetValue,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => NumberFormat::FORMAT_NUMBER,
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
