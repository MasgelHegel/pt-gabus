<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\InvoiceRepositoryInterface;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/** @extends BaseRepository<Invoice> */
class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    /** @return LengthAwarePaginator<Invoice> */
    public function paginateForCustomer(int $customerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['salesOrder', 'items.product', 'latestPayment'])
            ->where('customer_id', $customerId);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('invoice_date')->paginate($perPage);
    }

    /** @return Collection<int, Invoice> */
    public function getOverdueInvoices(): Collection
    {
        return $this->model->newQuery()
            ->with(['customer'])
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::PaymentUploaded->value])
            ->where('due_date', '<', now())
            ->get();
    }

    public function generateNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';
        $last   = $this->model->newQuery()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
