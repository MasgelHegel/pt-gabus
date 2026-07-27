<?php

declare(strict_types=1);

namespace App\Actions\Stock;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\StockMovement;

class AdjustStockAction
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepo,
    ) {}

    public function __invoke(int $productId, int $warehouseId, int $quantity, string $type, ?string $reference = null, ?string $notes = null): StockMovement
    {
        // Adjust product stock
        $delta = $type === 'in' ? $quantity : -$quantity;
        $this->productRepo->adjustStock($productId, $delta);

        // Record movement
        /** @var StockMovement */
        return StockMovement::create([
            'product_id'   => $productId,
            'warehouse_id' => $warehouseId,
            'type'         => $type,
            'quantity'     => $quantity,
            'reference'    => $reference,
            'notes'        => $notes,
            'created_by'   => auth()->id(),
        ]);
    }
}
