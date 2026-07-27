<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_number',
        'sales_order_id',
        'courier',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'customer_confirmed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'shipped_at'            => 'datetime',
        'delivered_at'          => 'datetime',
        'customer_confirmed_at' => 'datetime',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function canBeConfirmedByCustomer(): bool
    {
        return $this->status === 'shipped' && $this->customer_confirmed_at === null;
    }
}
