<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Portal;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalInvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly PaymentService $paymentService,
        private readonly CustomerRepositoryInterface $customerRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $customer = $this->customerRepo->findByUserId($request->user()->id);
        if (! $customer) {
            return ApiResponse::notFound('Data customer tidak ditemukan');
        }

        $filters = $request->only(['status']);
        $perPage = min((int) $request->input('per_page', 15), 100);

        $invoices = $this->invoiceService->paginateForCustomer($customer->id, $filters, $perPage);

        return ApiResponse::success([
            'data' => $invoices->map(fn ($inv) => [
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'invoice_date'   => $inv->invoice_date->toDateString(),
                'due_date'       => $inv->due_date->toDateString(),
                'is_overdue'     => $inv->due_date->isPast() && ! in_array($inv->status->value, ['paid', 'cancelled']),
                'total_amount'   => (float) $inv->total_amount,
                'status'         => ['value' => $inv->status->value, 'label' => $inv->status->label(), 'color' => $inv->status->color()],
                'latest_payment' => $inv->latestPayment ? [
                    'amount'         => (float) $inv->latestPayment->amount,
                    'payment_date'   => $inv->latestPayment->payment_date->toDateString(),
                    'status'         => $inv->latestPayment->status->label(),
                ] : null,
            ]),
            'meta' => [
                'total'        => $invoices->total(),
                'per_page'     => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerRepo->findByUserId($request->user()->id);
        if (! $customer) {
            return ApiResponse::notFound('Data customer tidak ditemukan');
        }

        $invoice = $this->invoiceService->findById($id);

        if ($invoice->customer_id !== $customer->id) {
            return ApiResponse::forbidden();
        }

        return ApiResponse::success([
            'id'             => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_date'   => $invoice->invoice_date->toDateString(),
            'due_date'       => $invoice->due_date->toDateString(),
            'subtotal'       => (float) $invoice->subtotal,
            'tax_amount'     => 0,
            'total_amount'   => (float) $invoice->total_amount,
            'status'         => ['value' => $invoice->status->value, 'label' => $invoice->status->label(), 'color' => $invoice->status->color()],
            'items'          => $invoice->load('items.product')->items->map(fn ($i) => [
                'product_name' => $i->product->name,
                'quantity'     => $i->quantity,
                'unit_price'   => (float) $i->unit_price,
                'subtotal'     => (float) $i->subtotal,
            ]),
            'payments'       => $invoice->load('payments')->payments->map(fn ($p) => [
                'payment_number' => $p->payment_number,
                'amount'         => (float) $p->amount,
                'payment_date'   => $p->payment_date->toDateString(),
                'status'         => $p->status->label(),
                'proof_url'      => $p->proof_url,
            ]),
        ]);
    }

    public function uploadPayment(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerRepo->findByUserId($request->user()->id);
        if (! $customer) {
            return ApiResponse::notFound('Data customer tidak ditemukan');
        }

        $invoice = $this->invoiceService->findById($id);

        if ($invoice->customer_id !== $customer->id) {
            return ApiResponse::forbidden();
        }

        $data = $request->validate([
            'proof_file'   => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'amount'       => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $payment = $this->paymentService->uploadProof(
            invoiceId:  $invoice->id,
            customerId: $customer->id,
            file:       $request->file('proof_file'),
            amount:     (float) $data['amount'],
            date:       $data['payment_date'],
        );

        return ApiResponse::created([
            'payment_number' => $payment->payment_number,
            'amount'         => (float) $payment->amount,
            'status'         => $payment->status->label(),
        ], 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.');
    }
}
