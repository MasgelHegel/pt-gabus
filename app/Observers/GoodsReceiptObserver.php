<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Stock\AdjustStockAction;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class GoodsReceiptObserver
{
    public function __construct(
        private readonly AdjustStockAction $adjustStock,
    ) {}

    /**
     * When goods receipt is created → add stock for each PO item
     */
    public function created(GoodsReceipt $receipt): void
    {
        $receipt->loadMissing(['purchaseOrder.items']);

        foreach ($receipt->purchaseOrder->items as $item) {
            ($this->adjustStock)(
                productId:   $item->product_id,
                warehouseId: $receipt->warehouse_id,
                quantity:    $item->quantity,
                type:        'in',
                reference:   $receipt->receipt_number,
                notes:       "Barang masuk dari PO #{$receipt->purchaseOrder->po_number}",
            );
        }
    }
}
