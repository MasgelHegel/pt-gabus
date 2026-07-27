<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<Product>
 */
interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /** @return LengthAwarePaginator<Product> */
    public function paginatePublic(array $filters, int $perPage = 20): LengthAwarePaginator;

    /** @return Collection<int, Product> */
    public function getLowStock(): Collection;

    public function adjustStock(int $productId, int $delta): void;
}
