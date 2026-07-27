<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Portal;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PortalOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
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

        $orders = $this->orderService->paginateForCustomer($customer->id, $filters, $perPage);

        return ApiResponse::success([
            'data' => $orders->map(fn ($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'status'       => ['value' => $o->status->value, 'label' => $o->status->label(), 'color' => $o->status->color()],
                'total_amount' => (float) $o->total_amount,
                'items_count'  => $o->items->count(),
                'items'        => $o->items->map(fn ($i) => [
                    'product_id'   => $i->product_id,
                    'product_name' => $i->product->name,
                    'quantity'     => $i->quantity,
                    'unit_price'   => (float) $i->unit_price,
                    'subtotal'     => (float) $i->subtotal,
                ]),
                'notes'        => $o->notes,
                'created_at'   => $o->created_at->toISOString(),
            ]),
            'meta' => [
                'total'        => $orders->total(),
                'per_page'     => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $customer = $this->customerRepo->findByUserId($request->user()->id);
        if (! $customer) {
            return ApiResponse::notFound('Data customer tidak ditemukan');
        }

        $data = $request->validate([
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
            'notes'               => ['nullable', 'string', 'max:500'],
        ]);

        // Enrich with prices from DB
        $items = collect($data['items'])->map(function ($item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);

            if ($product->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => ["Stok {$product->name} tidak mencukupi (tersedia: {$product->stock})"],
                ]);
            }

            return [
                'product_id' => $product->id,
                'quantity'   => $item['quantity'],
                'unit_price' => (float) $product->sell_price,
            ];
        })->toArray();

        $order = $this->orderService->createCustomerOrder($customer->id, $items, $data['notes'] ?? null);

        return ApiResponse::created([
            'id'           => $order->id,
            'order_number' => $order->order_number,
            'whatsapp_url' => $order->getWhatsAppUrl(),
            'status'       => ['value' => $order->status->value, 'label' => $order->status->label()],
            'total_amount' => (float) $order->total_amount,
            'items'        => $order->items->map(fn ($i) => [
                'product_name' => $i->product->name,
                'quantity'     => $i->quantity,
                'unit_price'   => (float) $i->unit_price,
                'subtotal'     => (float) $i->subtotal,
            ]),
        ], 'Order berhasil dibuat');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerRepo->findByUserId($request->user()->id);
        if (! $customer) {
            return ApiResponse::notFound('Data customer tidak ditemukan');
        }

        $order = $this->orderService->findById($id);

        if ($order->customer_id !== $customer->id) {
            return ApiResponse::forbidden();
        }

        return ApiResponse::success([
            'id'           => $order->id,
            'order_number' => $order->order_number,
            'whatsapp_url' => $order->getWhatsAppUrl(),
            'status'       => ['value' => $order->status->value, 'label' => $order->status->label(), 'color' => $order->status->color()],
            'total_amount' => (float) $order->total_amount,
            'notes'        => $order->notes,
            'items'        => $order->load('items.product')->items->map(fn ($i) => [
                'product_id'   => $i->product_id,
                'product_name' => $i->product->name,
                'quantity'     => $i->quantity,
                'unit_price'   => (float) $i->unit_price,
                'subtotal'     => (float) $i->subtotal,
            ]),
            'sales_order'  => $order->salesOrder ? [
                'id'        => $order->salesOrder->id,
                'so_number' => $order->salesOrder->so_number,
                'status'    => $order->salesOrder->status->label(),
                'shipment'  => $order->salesOrder->relationLoaded('shipment') && $order->salesOrder->shipment ? [
                    'id'                      => $order->salesOrder->shipment->id,
                    'courier'                 => $order->salesOrder->shipment->courier,
                    'tracking_number'         => $order->salesOrder->shipment->tracking_number,
                    'status'                  => $order->salesOrder->shipment->status,
                    'customer_confirmed_at'   => $order->salesOrder->shipment->customer_confirmed_at?->toISOString(),
                    'can_confirm'             => $order->salesOrder->shipment->canBeConfirmedByCustomer(),
                ] : null,
            ] : null,
            'created_at'   => $order->created_at->toISOString(),
        ]);
    }
}
