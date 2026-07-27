<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\InvoiceRepositoryInterface;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SalesOrder;

class CreateInvoiceFromSalesOrderAction
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepo,
        private readonly CustomerRepositoryInterface $customerRepo,
    ) {}

    public function __invoke(SalesOrder $salesOrder, int $dueDays = 30): Invoice
    {
        $salesOrder->loadMissing('items.product', 'customer');

        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepo->create([
            'invoice_number'  => $this->invoiceRepo->generateNumber(),
            'sales_order_id'  => $salesOrder->id,
            'customer_id'     => $salesOrder->customer_id,
            'invoice_date'    => now(),
            'due_date'        => now()->addDays($dueDays),
            'subtotal'        => $salesOrder->subtotal,
            'tax_amount'      => 0,
            'total_amount'    => $salesOrder->total_amount,
            'status'          => InvoiceStatus::Unpaid,
        ]);

        foreach ($salesOrder->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal'   => $item->subtotal,
            ]);
        }

        // NOTE: piutang_balance di-increment oleh OrderWorkflowService::adminApproveOrder
        // Jangan increment di sini untuk menghindari double-count.

        return $invoice->fresh(['items.product', 'customer']);
    }
}
