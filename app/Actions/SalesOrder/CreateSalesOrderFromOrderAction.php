<?php

declare(strict_types=1);

namespace App\Actions\SalesOrder;

use App\Enums\OrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Order;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;

class CreateSalesOrderFromOrderAction
{
    public function __invoke(Order $order, int $approvedBy, float $taxRate = 0.0): SalesOrder
    {
        $order->loadMissing('items.product', 'customer');

        $subtotal  = (float) $order->total_amount;
        $taxAmount = 0.0;
        $total     = $subtotal;

        $soNumber = 'SO-' . now()->format('Ym') . '-' . str_pad((string) (SalesOrder::query()->count() + 1), 4, '0', STR_PAD_LEFT);

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

        return $so->fresh(['items.product', 'customer', 'approvedBy']);
    }
}
