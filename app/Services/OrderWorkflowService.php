<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Journal\CreateJournalEntryAction;
use App\Actions\Stock\AdjustStockAction;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QCStatus;
use App\Enums\SalesOrderStatus;
use App\Events\OrderStatusChanged;
use App\Events\PaymentVerified;
use App\Events\ShipmentConfirmed;
use App\Models\Account;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\QCCheck;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Support\Facades\DB;

class OrderWorkflowService
{
    public function __construct(
        private readonly AdjustStockAction      $adjustStock,
        private readonly CreateJournalEntryAction $createJournal,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Sales meninjau order customer
    // ─────────────────────────────────────────────────────────────────────────
    public function salesReviewOrder(Order $order, int $salesId): Order
    {
        $oldStatus = $order->status;

        $order->update([
            'status'   => OrderStatus::SalesReviewed,
            'sales_id' => $salesId,
        ]);

        $order = $order->fresh();

        OrderStatusChanged::dispatch($order, $oldStatus, $order->status);

        return $order;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Admin approve → auto buat Sales Order + Invoice + Piutang
    // ─────────────────────────────────────────────────────────────────────────
    public function adminApproveOrder(Order $order, int $approvedBy, float $taxRate = 0.0, ?int $dueDays = null, ?\Carbon\Carbon $dueDate = null): SalesOrder
    {
        $oldStatus = $order->status;

        $order->loadMissing('customer');
        $resolvedDueDays = $dueDays ?? $order->customer?->due_period_days ?? 30;

        return DB::transaction(function () use ($order, $approvedBy, $oldStatus, $resolvedDueDays, $dueDate) {
            $order->loadMissing(['items.product', 'customer']);

            $subtotal  = (float) $order->total_amount;
            $taxAmount = 0.0;
            $total     = $subtotal;

            $soNumber = 'SO-' . now()->format('Ym') . '-'
                . str_pad((string) (SalesOrder::count() + 1), 4, '0', STR_PAD_LEFT);

            /** @var SalesOrder $so */
            $so = SalesOrder::create([
                'so_number'    => $soNumber,
                'order_id'     => $order->id,
                'customer_id'  => $order->customer_id,
                'sales_id'     => $order->sales_id,
                'approved_by'  => $approvedBy,
                'status'       => SalesOrderStatus::Processing,
                'subtotal'     => $subtotal,
                'tax_amount'   => $taxAmount,
                'total_amount' => $total,
                'notes'        => $order->notes,
            ]);

            foreach ($order->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'product_id'     => $item->product_id,
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->unit_price,
                    'subtotal'       => $item->subtotal,
                ]);
            }

            $order->update(['status' => OrderStatus::SOCreated]);

            // Auto-create Invoice
            $invoice = $this->createInvoiceFromSO($so, $resolvedDueDays, $dueDate);

            // Auto-create Piutang
            Customer::where('id', $so->customer_id)
                ->increment('piutang_balance', $total);

            // Jurnal: Dr Piutang Usaha / Cr Pendapatan Penjualan
            $this->journalInvoiceCreated($invoice, $total);

            OrderStatusChanged::dispatch($order->fresh(), $oldStatus, $order->status);

            return $so->fresh(['items.product', 'customer', 'invoice']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Buat Purchase Order ke supplier
    // ─────────────────────────────────────────────────────────────────────────
    public function createPurchaseOrder(SalesOrder $so, int $supplierId, ?string $notes = null): PurchaseOrder
    {
        return DB::transaction(function () use ($so, $supplierId, $notes) {
            $so->loadMissing('items.product');

            $totalAmount = (float) $so->total_amount;

            $poNumber = 'PO-' . now()->format('Ym') . '-'
                . str_pad((string) (PurchaseOrder::count() + 1), 4, '0', STR_PAD_LEFT);

            /** @var PurchaseOrder $po */
            $po = PurchaseOrder::create([
                'po_number'      => $poNumber,
                'sales_order_id' => $so->id,
                'supplier_id'    => $supplierId,
                'status'         => PurchaseOrderStatus::Ordered,
                'total_amount'   => $totalAmount,
                'notes'          => $notes,
            ]);

            foreach ($so->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item->product_id,
                    'quantity'          => $item->quantity,
                    'unit_price'        => $item->unit_price,
                    'subtotal'          => $item->subtotal,
                ]);
            }

            return $po->fresh(['items.product', 'supplier']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Terima barang masuk (Goods Receipt)
    // ─────────────────────────────────────────────────────────────────────────
    public function processGoodsReceipt(PurchaseOrder $po, int $warehouseId, int $receivedBy, ?string $notes = null): GoodsReceipt
    {
        return DB::transaction(function () use ($po, $warehouseId, $receivedBy, $notes) {
            $receiptNumber = 'GR-' . now()->format('Ym') . '-'
                . str_pad((string) (GoodsReceipt::count() + 1), 4, '0', STR_PAD_LEFT);

            /** @var GoodsReceipt $receipt */
            $receipt = GoodsReceipt::create([
                'receipt_number'    => $receiptNumber,
                'purchase_order_id' => $po->id,
                'warehouse_id'      => $warehouseId,
                'received_date'     => now(),
                'received_by'       => $receivedBy,
                'notes'             => $notes,
            ]);

            // Buat QC Check otomatis dengan status pending
            QCCheck::create([
                'goods_receipt_id' => $receipt->id,
                'inspector_id'     => null,
                'status'           => QCStatus::Pending,
                'passed_qty'       => 0,
                'failed_qty'       => 0,
            ]);

            $po->update(['status' => PurchaseOrderStatus::GoodsReceived]);

            return $receipt->fresh(['purchaseOrder', 'qcCheck']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Verifikasi QC — jika lolos, update stok
    // ─────────────────────────────────────────────────────────────────────────
    public function verifyQCCheck(GoodsReceipt $receipt, bool $isPassed, int $inspectorId, ?string $notes = null): QCCheck
    {
        return DB::transaction(function () use ($receipt, $isPassed, $inspectorId, $notes) {
            $receipt->loadMissing(['purchaseOrder.items', 'qcCheck']);

            $totalQty = $receipt->purchaseOrder->items->sum('quantity');

            /** @var QCCheck $qc */
            $qc = $receipt->qcCheck ?? QCCheck::create([
                'goods_receipt_id' => $receipt->id,
                'inspector_id'     => $inspectorId,
                'status'           => QCStatus::Pending,
                'passed_qty'       => 0,
                'failed_qty'       => 0,
            ]);

            $qc->update([
                'inspector_id' => $inspectorId,
                'status'       => $isPassed ? QCStatus::Passed : QCStatus::Failed,
                'passed_qty'   => $isPassed ? $totalQty : 0,
                'failed_qty'   => $isPassed ? 0 : $totalQty,
                'notes'        => $notes,
            ]);

            if ($isPassed) {
                // Update stok setiap produk di PO
                foreach ($receipt->purchaseOrder->items as $item) {
                    ($this->adjustStock)(
                        productId:   $item->product_id,
                        warehouseId: $receipt->warehouse_id,
                        quantity:    $item->quantity,
                        type:        'in',
                        reference:   $receipt->receipt_number,
                        notes:       "Barang masuk lolos QC dari PO #{$receipt->purchaseOrder->po_number}",
                    );
                }

                $receipt->purchaseOrder->update(['status' => PurchaseOrderStatus::QCPassed]);
            }

            return $qc->fresh();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Kirim barang (Shipment)
    // ─────────────────────────────────────────────────────────────────────────
    public function shipSalesOrder(SalesOrder $so, string $courier, string $trackingNumber, ?string $notes = null): Shipment
    {
        return DB::transaction(function () use ($so, $courier, $trackingNumber, $notes) {
            $so->loadMissing('items');

            $shipNumber = 'SHP-' . now()->format('Ym') . '-'
                . str_pad((string) (Shipment::count() + 1), 4, '0', STR_PAD_LEFT);

            /** @var Shipment $shipment */
            $shipment = Shipment::create([
                'shipment_number' => $shipNumber,
                'sales_order_id'  => $so->id,
                'courier'         => $courier,
                'tracking_number' => $trackingNumber,
                'shipped_at'      => now(),
                'status'          => 'shipped',
                'notes'           => $notes,
            ]);

            $warehouse = \App\Models\Warehouse::first();
            if (! $warehouse) {
                $warehouse = \App\Models\Warehouse::create([
                    'code'    => 'GD-01',
                    'name'    => 'Gudang Utama',
                    'address' => 'Jl. Industri No. 1, Jakarta',
                ]);
            }
            $warehouseId = $warehouse->id;

            foreach ($so->items as $item) {
                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'product_id'  => $item->product_id,
                    'quantity'    => $item->quantity,
                ]);

                // Kurangi stok produk saat barang dikirim
                ($this->adjustStock)(
                    productId:   $item->product_id,
                    warehouseId: $warehouseId,
                    quantity:    $item->quantity,
                    type:        'out',
                    reference:   $shipNumber,
                    notes:       "Barang dikirim untuk SO #{$so->so_number}",
                );
            }

            $so->update(['status' => SalesOrderStatus::Shipped]);

            return $shipment->fresh(['items.product']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. Customer konfirmasi barang diterima
    // ─────────────────────────────────────────────────────────────────────────
    public function customerConfirmDelivery(Shipment $shipment): Shipment
    {
        if (! $shipment->canBeConfirmedByCustomer()) {
            throw new \RuntimeException('Shipment cannot be confirmed by customer at this stage.');
        }

        $shipment->update([
            'status'                => 'customer_confirmed',
            'customer_confirmed_at' => now(),
        ]);

        $shipment = $shipment->fresh();

        ShipmentConfirmed::dispatch($shipment);

        return $shipment;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. Verifikasi konfirmasi customer — terima / tolak
    // ─────────────────────────────────────────────────────────────────────────
    public function markShipmentDelivered(Shipment $shipment): Shipment
    {
        $shipment->update([
            'status'       => 'delivered',
            'delivered_at' => now(),
        ]);

        $shipment->salesOrder?->update(['status' => SalesOrderStatus::Delivered]);

        return $shipment->fresh();
    }

    public function rejectCustomerConfirmation(Shipment $shipment): Shipment
    {
        if ($shipment->status !== 'customer_confirmed') {
            throw new \RuntimeException('Only customer-confirmed shipments can be rejected.');
        }

        $shipment->update([
            'status'                => 'shipped',
            'customer_confirmed_at' => null,
        ]);

        return $shipment->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. Verifikasi / tolak pembayaran customer
    // ─────────────────────────────────────────────────────────────────────────
    public function verifyPayment(Payment $payment, bool $isApproved, int $verifiedBy, ?int $accountId = null, ?string $rejectionReason = null): Payment
    {
        $result = DB::transaction(function () use ($payment, $isApproved, $verifiedBy, $accountId, $rejectionReason) {
            $payment->loadMissing(['invoice', 'customer']);

            if ($isApproved) {
                $payment->update([
                    'status'      => PaymentStatus::Verified,
                    'account_id'  => $accountId,
                    'verified_by' => $verifiedBy,
                    'verified_at' => now(),
                ]);

                // Invoice → Lunas
                $payment->invoice->update(['status' => InvoiceStatus::Paid]);

                // Piutang customer berkurang
                Customer::where('id', $payment->customer_id)
                    ->decrement('piutang_balance', (float) $payment->amount);

                // Jurnal: Dr Kas/Bank / Cr Piutang Usaha
                $this->journalPaymentVerified($payment, $accountId);
            } else {
                $payment->update([
                    'status'           => PaymentStatus::Rejected,
                    'rejection_reason' => $rejectionReason,
                ]);

                // Invoice kembali ke Unpaid
                $payment->invoice->update(['status' => InvoiceStatus::Unpaid]);
            }

            return $payment->fresh(['invoice', 'customer']);
        });

        if ($isApproved) {
            PaymentVerified::dispatch($result);
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9. Wrapper untuk pembuatan order (delegasi ke OrderService)
    // ─────────────────────────────────────────────────────────────────────────
    public function createCustomerOrder(int $customerId, array $items, ?string $notes = null): Order
    {
        return app(\App\Services\OrderService::class)->createCustomerOrder($customerId, $items, $notes);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 10. Wrapper untuk upload bukti pembayaran (simulasi file upload di test)
    // ─────────────────────────────────────────────────────────────────────────
    public function uploadPaymentProof(Invoice $invoice, string $proofFile, float $amount): Payment
    {
        return DB::transaction(function () use ($invoice, $proofFile, $amount) {
            $paymentNumber = 'PAY-' . now()->format('Ym') . '-'
                . str_pad((string) (Payment::count() + 1), 4, '0', STR_PAD_LEFT);

            /** @var Payment $payment */
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id'     => $invoice->id,
                'customer_id'    => $invoice->customer_id,
                'amount'         => $amount,
                'payment_date'   => now()->toDateString(),
                'proof_file'     => $proofFile,
                'status'         => PaymentStatus::Pending,
            ]);

            $invoice->update(['status' => InvoiceStatus::PaymentUploaded]);

            return $payment->fresh(['invoice', 'customer']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers private
    // ─────────────────────────────────────────────────────────────────────────

    // ─────────────────────────────────────────────────────────────────────────
    // Jurnal otomatis
    // ─────────────────────────────────────────────────────────────────────────

    private function journalInvoiceCreated(Invoice $invoice, float $amount): void
    {
        // Akun 1120 = Piutang Usaha, 4100 = Pendapatan Penjualan
        $receivable = Account::where('code', '1120')->first();
        $revenue    = Account::where('code', '4100')->first();

        if (! $receivable || ! $revenue) {
            return;
        }

        ($this->createJournal)(
            description: "Penjualan Invoice #{$invoice->invoice_number}",
            lines: [
                ['account_id' => $receivable->id, 'debit' => $amount, 'credit' => 0.0],
                ['account_id' => $revenue->id,    'debit' => 0.0,     'credit' => $amount],
            ],
            reference: $invoice->invoice_number,
        );
    }

    private function journalPaymentVerified(Payment $payment, ?int $accountId): void
    {
        $cash       = $accountId
            ? Account::find($accountId)
            : Account::where('is_cash_bank', true)->first();
        $receivable = Account::where('code', '1120')->first();

        if (! $cash || ! $receivable) {
            return;
        }

        ($this->createJournal)(
            description: "Penerimaan Pembayaran #{$payment->payment_number}",
            lines: [
                ['account_id' => $cash->id,       'debit' => (float) $payment->amount, 'credit' => 0.0],
                ['account_id' => $receivable->id, 'debit' => 0.0, 'credit' => (float) $payment->amount],
            ],
            reference: $payment->payment_number,
        );
    }

    private function createInvoiceFromSO(SalesOrder $so, int $dueDays = 30, ?\Carbon\Carbon $dueDate = null): Invoice
    {
        $so->loadMissing('items');

        $invNumber = 'INV-' . now()->format('Ym') . '-'
            . str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT);

        /** @var Invoice $invoice */
        $invoice = Invoice::create([
            'invoice_number' => $invNumber,
            'sales_order_id' => $so->id,
            'customer_id'    => $so->customer_id,
            'invoice_date'   => now(),
            'due_date'       => $dueDate ?? now()->addDays($dueDays),
            'subtotal'       => $so->subtotal,
            'tax_amount'     => 0,
            'total_amount'   => $so->total_amount,
            'status'         => InvoiceStatus::Unpaid,
        ]);

        foreach ($so->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal'   => $item->subtotal,
            ]);
        }

        return $invoice;
    }

}
