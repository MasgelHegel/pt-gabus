<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'sales_id',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'status'       => OrderStatus::class,
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function salesOrder(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SalesOrder::class);
    }

    public function getWhatsAppUrl(): string
    {
        $this->loadMissing(['customer', 'items.product']);

        $adminPhone = '6285211507166';

        $customerName = $this->customer?->name ?? 'Tamu';
        $dapurName = $this->customer?->company_name ?? '—';
        $phone = $this->customer?->phone ?? '—';
        $address = $this->customer?->address ?? '';

        $itemsText = "";
        foreach ($this->items as $item) {
            $itemsText .= "- " . ($item->product?->name ?? 'Produk') . " x" . $item->quantity . "\n";
        }

        $text = "Halo Admin, saya ingin konfirmasi pesanan Gas LPG saya:\n\n";
        $text .= "*No. Order:* " . $this->order_number . "\n";
        $text .= "*Nama Dapur/SPPG:* " . $dapurName . "\n";
        $text .= "*Nama Pelanggan:* " . $customerName . "\n";
        $text .= "*No. HP:* " . $phone . "\n";

        if ($address) {
            $text .= "*Alamat:* " . $address . "\n";
        }

        $text .= "\n*Detail Pesanan:*\n" . $itemsText;
        $text .= "\n*Total:* Rp " . number_format((float)$this->total_amount, 0, ',', '.') . "\n";

        if ($this->notes) {
            if (str_contains($this->notes, '📍 Nama Dapur:')) {
                // Extract "💬 Catatan: " part if it exists
                if (preg_replace('/.*💬 Catatan:\s*/s', '', $this->notes) !== $this->notes) {
                    $catatan = preg_replace('/.*💬 Catatan:\s*/s', '', $this->notes);
                    if ($catatan) {
                        $text .= "*Catatan:* " . trim($catatan) . "\n";
                    }
                }
            } else {
                $text .= "*Catatan:* " . $this->notes . "\n";
            }
        }

        $text .= "\nMohon dibantu untuk proses pengirimannya. Terima kasih!";

        return 'https://api.whatsapp.com/send?phone=' . $adminPhone . '&text=' . rawurlencode($text);
    }
}
