<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'unit',
        'buy_price',
        'sell_price',
        'stock',
        'min_stock',
        'image',
        'description',
    ];

    protected $casts = [
        'buy_price'  => 'decimal:2',
        'sell_price' => 'decimal:2',
        'stock'      => 'integer',
        'min_stock'  => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        return match (true) {
            str_starts_with((string) $this->sku, 'GAS-12') => asset('image/12kg.png'),
            str_starts_with((string) $this->sku, 'GAS-50') => asset('image/50kg.png'),
            default                                        => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=600&q=80',
        };
    }
}
