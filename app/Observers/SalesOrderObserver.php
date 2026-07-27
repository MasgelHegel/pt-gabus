<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Invoice\CreateInvoiceFromSalesOrderAction;
use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;

class SalesOrderObserver
{
    public function __construct(
        private readonly CreateInvoiceFromSalesOrderAction $createInvoice,
    ) {}

    /**
     * Auto-create invoice when SO moves to ReadyToShip
     */
    public function updated(SalesOrder $so): void
    {
        if (! $so->wasChanged('status')) {
            return;
        }

        if ($so->status !== SalesOrderStatus::ReadyToShip) {
            return;
        }

        // Only create invoice if not yet exists
        if ($so->invoice()->exists()) {
            return;
        }

        ($this->createInvoice)($so);
    }
}
