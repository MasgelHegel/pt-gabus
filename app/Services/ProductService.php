<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** @extends BaseService<Product> */
class ProductService extends BaseService
{
    public function __construct(
        ProductRepositoryInterface $repository,
    ) {
        parent::__construct($repository);
    }

    /** @return LengthAwarePaginator<Product> */
    public function paginatePublic(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->paginatePublic($filters, $perPage);
    }

    public function findById(int $id): Product
    {
        /** @var Product */
        return $this->repository->findById($id);
    }
}
