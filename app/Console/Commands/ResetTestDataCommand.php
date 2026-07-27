<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Shipment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetTestDataCommand extends Command
{
    protected $signature   = 'reset:test-data {--force : Skip konfirmasi}';
    protected $description = 'Hapus semua data transaksi test (order, SO, invoice, payment, jurnal, shipment, PO). Data master (produk, customer, user) tetap aman.';

    public function handle(): int
    {
        $this->warn('⚠️  Peringatan: Semua data transaksi akan dihapus permanen!');
        $this->line('   Yang dihapus: Order, SalesOrder, Invoice, Payment, JournalEntry, Shipment, PurchaseOrder, GoodsReceipt, AuditLog');
        $this->line('   Yang TIDAK dihapus: User, Customer, Product, Supplier, Account, Category');
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Yakin ingin menghapus semua data transaksi?', false)) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        $this->info('Menghapus data...');

        DB::transaction(function (): void {
            // Urutan penting — hapus child dulu sebelum parent

            // 1. Audit logs
            $this->line('  → AuditLog');
            AuditLog::query()->forceDelete();

            // 2. Jurnal entries & lines
            $this->line('  → JournalEntry & JournalLine');
            DB::table('journal_lines')->delete();
            JournalEntry::query()->delete();

            // 3. Payments (tidak pakai SoftDeletes)
            $this->line('  → Payment');
            Payment::query()->delete();

            // 4. Invoice items & invoices
            $this->line('  → InvoiceItem & Invoice');
            DB::table('invoice_items')->delete();
            Invoice::withTrashed()->forceDelete();

            // 5. Shipments & shipment_items & stock_movements
            $this->line('  → ShipmentItem, Shipment, StockMovement');
            DB::table('shipment_items')->delete();
            DB::table('stock_movements')->delete();
            Shipment::query()->delete();

            // 6. Goods receipts (tidak ada goods_receipt_items, tabel langsung goods_receipts)
            $this->line('  → GoodsReceipt & QCCheck');
            DB::table('qc_checks')->delete();
            GoodsReceipt::query()->delete();

            // 7. PO items & POs
            $this->line('  → PurchaseOrderItem & PurchaseOrder');
            DB::table('purchase_order_items')->delete();
            PurchaseOrder::withTrashed()->forceDelete();

            // 8. SO items & SOs
            $this->line('  → SalesOrderItem & SalesOrder');
            DB::table('sales_order_items')->delete();
            SalesOrder::withTrashed()->forceDelete();

            // 9. Order items & orders
            $this->line('  → OrderItem & Order');
            DB::table('order_items')->delete();
            Order::withTrashed()->forceDelete();

            // 10. Reset piutang semua customer ke 0
            $this->line('  → Reset piutang_balance customer → 0');
            Customer::query()->update(['piutang_balance' => 0]);
        });

        $this->newLine();
        $this->info('✅ Semua data transaksi berhasil dihapus. Aplikasi siap digunakan.');

        return self::SUCCESS;
    }
}
