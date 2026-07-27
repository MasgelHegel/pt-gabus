<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ErpStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $pendingOrders  = Order::whereIn('status', [
            OrderStatus::Submitted->value,
            OrderStatus::SalesReviewed->value,
            OrderStatus::AdminApproved->value,
        ])->count();

        $unpaidInvoices = Invoice::whereIn('status', [
            InvoiceStatus::Unpaid->value,
            InvoiceStatus::Overdue->value,
        ])->count();

        $unpaidTotal = (float) Invoice::whereIn('status', [
            InvoiceStatus::Unpaid->value,
            InvoiceStatus::Overdue->value,
            InvoiceStatus::PaymentUploaded->value,
        ])->sum('total_amount');

        $pendingPayments = Payment::where('status', \App\Enums\PaymentStatus::Pending)->count();

        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->count();

        $revenueThisMonth = (float) Invoice::where('status', InvoiceStatus::Paid->value)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('total_amount');

        return [
            Stat::make('Order Menunggu', number_format($pendingOrders))
                ->description('Perlu ditindaklanjuti')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->chart([3, 5, 2, 8, 4, 6, $pendingOrders]),

            Stat::make('Invoice Belum Lunas', number_format($unpaidInvoices))
                ->description('Rp ' . number_format($unpaidTotal, 0, ',', '.'))
                ->descriptionIcon('heroicon-o-document-text')
                ->color('danger')
                ->chart([2, 4, 3, 7, 5, 3, $unpaidInvoices]),

            Stat::make('Bukti Bayar Pending', number_format($pendingPayments))
                ->description('Menunggu verifikasi admin')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('info')
                ->chart([1, 2, 1, 3, 2, 4, $pendingPayments]),

            Stat::make('Revenue Bulan Ini', 'Rp ' . number_format($revenueThisMonth, 0, ',', '.'))
                ->description('Invoice lunas ' . now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('Stok Rendah', number_format($lowStockCount) . ' produk')
                ->description('Di bawah minimum stok')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Total Customer', number_format(Customer::count()))
                ->description('Customer aktif terdaftar')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),
        ];
    }
}
