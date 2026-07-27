<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/** @extends BaseRepository<Product> */
class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /** @return LengthAwarePaginator<Product> */
    public function paginatePublic(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with('category')
            ->where('stock', '>', 0);

        if (! empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%"));
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $query->orderBy('name');

        return $query->paginate($perPage);
    }

    /** @return Collection<int, Product> */
    public function getLowStock(): Collection
    {
        return $this->model->newQuery()
            ->whereColumn('stock', '<=', 'min_stock')
            ->with('category')
            ->orderBy('stock')
            ->get();
    }

    public function adjustStock(int $productId, int $delta): void
    {
        $this->model->newQuery()
            ->where('id', $productId)
            ->increment('stock', $delta);
    }
}
