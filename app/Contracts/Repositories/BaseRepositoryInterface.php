<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface BaseRepositoryInterface
{
    /** @return Collection<int, TModel> */
    public function all(array $columns = ['*']): Collection;

    /** @return LengthAwarePaginator<TModel> */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /** @return TModel */
    public function findById(int $id, array $columns = ['*']): Model;

    /** @return TModel|null */
    public function findByIdOrNull(int $id, array $columns = ['*']): ?Model;

    /** @return TModel */
    public function create(array $data): Model;

    /** @return TModel */
    public function update(int $id, array $data): Model;

    public function delete(int $id): bool;

    public function forceDelete(int $id): bool;

    /** @return TModel|null */
    public function firstWhere(array $conditions): ?Model;

    /** @return Collection<int, TModel> */
    public function where(array $conditions): Collection;

    public function exists(array $conditions): bool;

    public function count(array $conditions = []): int;
}
