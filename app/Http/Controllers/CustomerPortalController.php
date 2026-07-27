<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use App\Services\OrderService;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepo,
        private readonly OrderService $orderService,
        private readonly OrderWorkflowService $orderWorkflow,
        private readonly PaymentService $paymentService,
    ) {}

    public function catalog(): View
    {
        $products   = Product::with('category')
            ->latest()
            ->paginate(20);

        $categories = Category::orderBy('name')->get();

        $customer = $this->getCustomer();

        return view('portal.catalog', compact('products', 'categories', 'customer'));
    }

    public function orders(Request $request): View
    {
        $customer = $this->getCustomer();
        if (! $customer) {
            return view('portal.no-customer');
        }

        $orders = Order::with(['items.product', 'salesOrder.shipment'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        return view('portal.orders', compact('orders', 'customer'));
    }

    public function confirmDelivery(int $shipment): RedirectResponse
    {
        $customer = $this->getCustomer();
        if (! $customer) {
            return back()->withErrors(['error' => 'Data customer tidak ditemukan']);
        }

        $shipment = Shipment::where('id', $shipment)
            ->whereHas('salesOrder', fn ($q) => $q->where('customer_id', $customer->id))
            ->firstOrFail();

        try {
            $this->orderWorkflow->customerConfirmDelivery($shipment);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Barang telah dikonfirmasi diterima. Menunggu verifikasi sales.');
    }

    public function createOrder(): View
    {
        $products   = Product::with('category')
            ->whereHas('category', fn ($q) => $q->where('slug', 'gas-lpg'))
            ->orderBy('name')
            ->get();
        $categories = Category::orderBy('name')->get();

        return view('portal.create-order', compact('products', 'categories'));
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $customer = $this->getCustomer();
        if (! $customer) {
            return back()->withErrors(['error' => 'Data customer tidak ditemukan']);
        }

        $data = $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $items = collect($data['items'])->map(function ($item) {
            $product = Product::findOrFail($item['product_id']);
            return [
                'product_id' => $product->id,
                'quantity'   => $item['quantity'],
                'unit_price' => (float) $product->sell_price,
            ];
        })->toArray();

        $order = $this->orderService->createCustomerOrder(
            $customer->id,
            $items,
            $data['notes'] ?? null
        );

        return redirect()
            ->route('portal.orders.index')
            ->with([
                'success' => "Order #{$order->order_number} berhasil dibuat!",
                'whatsapp_order_number' => $order->order_number,
            ]);
    }

    public function invoices(Request $request): View
    {
        $customer = $this->getCustomer();
        if (! $customer) {
            return view('portal.no-customer');
        }

        $invoices = Invoice::with(['items.product', 'payments'])
            ->where('customer_id', $customer->id)
            ->latest('invoice_date')
            ->paginate(15);

        // Hitung piutang real-time dari invoice yang belum lunas
        // agar tampilan tidak drift dari kolom piutang_balance yang di-cache
        $piutangReal = Invoice::where('customer_id', $customer->id)
            ->whereNotIn('status', [
                \App\Enums\InvoiceStatus::Paid,
                \App\Enums\InvoiceStatus::Cancelled,
            ])
            ->sum('total_amount');

        // Sync kolom piutang_balance supaya tetap konsisten
        if ((float) $customer->piutang_balance !== (float) $piutangReal) {
            $customer->update(['piutang_balance' => $piutangReal]);
            $customer->piutang_balance = $piutangReal;
        }

        return view('portal.invoices', compact('invoices', 'customer'));
    }

    public function uploadPayment(Request $request, int $invoice): RedirectResponse
    {
        $customer = $this->getCustomer();
        if (! $customer) {
            return back()->withErrors(['error' => 'Data customer tidak ditemukan']);
        }

        $inv = Invoice::where('id', $invoice)
            ->where('customer_id', $customer->id)
            ->firstOrFail();

        $data = $request->validate([
            'proof_file'   => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'amount'       => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $this->paymentService->uploadProof(
            invoiceId:  $inv->id,
            customerId: $customer->id,
            file:       $request->file('proof_file'),
            amount:     (float) $data['amount'],
            date:       $data['payment_date'],
        );

        return back()->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.');
    }

    private function getCustomer(): ?\App\Models\Customer
    {
        if (! auth()->check()) {
            return null;
        }
        return $this->customerRepo->findByUserId(auth()->id());
    }
}
