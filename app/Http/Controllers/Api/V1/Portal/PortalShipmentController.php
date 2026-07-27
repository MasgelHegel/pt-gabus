<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Portal;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\Shipment;
use App\Services\OrderWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalShipmentController extends Controller
{
    public function __construct(
        private readonly OrderWorkflowService $orderWorkflow,
        private readonly CustomerRepositoryInterface $customerRepo,
    ) {}

    public function confirm(Request $request, int $id): JsonResponse
    {
        $customer = $this->customerRepo->findByUserId($request->user()->id);
        if (! $customer) {
            return ApiResponse::notFound('Data customer tidak ditemukan');
        }

        $shipment = Shipment::where('id', $id)
            ->whereHas('salesOrder', fn ($q) => $q->where('customer_id', $customer->id))
            ->first();

        if (! $shipment) {
            return ApiResponse::notFound('Pengiriman tidak ditemukan');
        }

        try {
            $shipment = $this->orderWorkflow->customerConfirmDelivery($shipment);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::success([
            'id'                      => $shipment->id,
            'status'                  => $shipment->status,
            'customer_confirmed_at'   => $shipment->customer_confirmed_at?->toISOString(),
        ], 'Barang berhasil dikonfirmasi diterima. Menunggu verifikasi sales.');
    }
}
