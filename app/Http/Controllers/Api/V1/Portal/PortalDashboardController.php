<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Portal;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalDashboardController extends Controller
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepo,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user     = $request->user();
        $customer = $this->customerRepo->findByUserId($user->id);

        if (! $customer) {
            return ApiResponse::notFound('Data customer tidak ditemukan');
        }

        $stats = [
            'piutang_balance' => (float) $customer->piutang_balance,
            'credit_limit'    => (float) $customer->credit_limit,
            'total_orders'    => Order::where('customer_id', $customer->id)->count(),
            'pending_orders'  => Order::where('customer_id', $customer->id)
                ->whereNotIn('status', ['so_created', 'cancelled', 'rejected'])
                ->count(),
            'unpaid_invoices' => Invoice::where('customer_id', $customer->id)
                ->whereIn('status', ['unpaid', 'overdue'])
                ->count(),
            'total_invoices'  => Invoice::where('customer_id', $customer->id)->count(),
        ];

        $recentOrders = Order::with(['items.product'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->limit(5)
            ->get();

        $unpaidInvoices = Invoice::with('salesOrder')
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'overdue', 'payment_uploaded'])
            ->latest('invoice_date')
            ->limit(5)
            ->get();

        return ApiResponse::success([
            'customer'       => [
                'id'              => $customer->id,
                'name'            => $customer->name,
                'code'            => $customer->code,
                'piutang_balance' => $stats['piutang_balance'],
                'credit_limit'    => $stats['credit_limit'],
            ],
            'stats'          => $stats,
            'recent_orders'  => $recentOrders,
            'unpaid_invoices' => $unpaidInvoices,
        ]);
    }
}
