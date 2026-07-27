<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicOrderController extends Controller
{
    public function create(): View
    {
        $products = Product::with('category')
            ->whereHas('category', fn ($q) => $q->where('slug', 'gas-lpg'))
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        return view('public.order', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_dapur'                     => 'required|string|max:255',
            'alamat'                         => 'nullable|string|max:500',
            'no_hp'                          => 'required|string|max:20',
            'jenis_tabung'                   => 'required|array|min:1',
            'jenis_tabung.*.product_id'      => 'required|exists:products,id',
            'jenis_tabung.*.qty'             => 'required|integer|min:1|max:999',
            'catatan'                        => 'nullable|string|max:500',
        ], [
            'nama_dapur.required'             => 'Nama SPPG / Dapur wajib diisi.',
            'no_hp.required'                  => 'Nomor HP / WhatsApp wajib diisi.',
            'jenis_tabung.required'           => 'Pilih minimal satu jenis tabung.',
            'jenis_tabung.*.product_id.required' => 'Pilih jenis tabung terlebih dahulu.',
            'jenis_tabung.*.qty.min'          => 'Jumlah tabung minimal 1.',
        ]);

        $items = collect($validated['jenis_tabung'])
            ->filter(fn ($i) => (int) $i['qty'] > 0)
            ->values()
            ->toArray();

        if (empty($items)) {
            return back()->withErrors(['jenis_tabung' => 'Masukkan jumlah tabung minimal 1.'])->withInput();
        }

        // Variabel yang perlu diakses di luar closure
        $orderNumber = null;
        $isLoggedIn  = Auth::check() && Auth::user()->isCustomer();

        DB::transaction(function () use ($validated, $items, &$orderNumber) {

            $user = Auth::user();

            // ── Customer record ──────────────────────────────────────────
            if ($user && $user->isCustomer()) {
                $customer = Customer::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'code'            => 'CUST-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                        'name'            => $user->name,
                        'email'           => $user->email,
                        'phone'           => $validated['no_hp'],
                        'company_name'    => $validated['nama_dapur'],
                        'credit_limit'    => 0,
                        'piutang_balance' => 0,
                    ]
                );
            } else {
                $customer = Customer::firstOrCreate(
                    ['phone' => $validated['no_hp']],
                    [
                        'code'            => 'PUB-' . strtoupper(substr(preg_replace('/\s+/', '', $validated['nama_dapur']), 0, 8)) . '-' . rand(100, 999),
                        'name'            => $validated['nama_dapur'],
                        'company_name'    => $validated['nama_dapur'],
                        'email'           => null,
                        'address'         => $validated['alamat'] ?? '',
                        'credit_limit'    => 0,
                        'piutang_balance' => 0,
                    ]
                );
            }

            // ── Hitung total ─────────────────────────────────────────────
            $totalAmount = 0;
            $orderItems  = [];

            foreach ($items as $item) {
                $product  = Product::findOrFail($item['product_id']);
                $qty      = (int) $item['qty'];
                $price    = (float) $product->sell_price;
                $subtotal = $qty * $price;
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'subtotal'   => $subtotal,
                ];
            }

            // ── Nomor order ──────────────────────────────────────────────
            $orderNumber = 'ORD-' . now()->format('Ymd') . '-'
                . str_pad((string) (Order::count() + 1), 4, '0', STR_PAD_LEFT);

            // ── Catatan untuk admin ──────────────────────────────────────
            $notes = "📍 Nama Dapur: {$validated['nama_dapur']}\n"
                . "📞 No HP: {$validated['no_hp']}\n"
                . ($validated['alamat'] ? "🏠 Alamat: {$validated['alamat']}\n" : '')
                . ($validated['catatan'] ? "💬 Catatan: {$validated['catatan']}" : '');

            // ── Simpan order ─────────────────────────────────────────────
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id'  => $customer->id,
                'status'       => OrderStatus::Submitted,
                'total_amount' => $totalAmount,
                'notes'        => $notes,
            ]);

            foreach ($orderItems as $oi) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $oi['product_id'],
                    'quantity'   => $oi['quantity'],
                    'unit_price' => $oi['unit_price'],
                    'subtotal'   => $oi['subtotal'],
                ]);
            }
        });

        // ── Redirect ─────────────────────────────────────────────────────
        if ($isLoggedIn) {
            // Customer login → langsung ke halaman pesanan di portal
            return redirect()->route('portal.orders.index')
                ->with('success', "Order #{$orderNumber} berhasil dibuat! Tim Sales akan segera memproses pesanan Anda.");
        }

        // Tamu → halaman sukses + ajakan daftar akun
        return redirect()->route('order.success')->with([
            'order_number' => $orderNumber,
            'nama_dapur'   => $validated['nama_dapur'],
            'no_hp'        => $validated['no_hp'],
        ]);
    }

    public function success(Request $request): View
    {
        $orderNumber = session('order_number') ?? $request->query('order_number');
        $order = null;

        if ($orderNumber) {
            $order = Order::with(['customer', 'items.product'])
                ->where('order_number', $orderNumber)
                ->first();
        }

        return view('public.order-success', compact('order'));
    }
}
