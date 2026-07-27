<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @template TModel of Model
 * @implements BaseRepositoryInterface<TModel>
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @param TModel $model
     */
    public function __construct(
        protected readonly Model $model,
    ) {}

    /** @return Collection<int, TModel> */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->newQuery()->get($columns);
    }

    /** @return LengthAwarePaginator<TModel> */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->newQuery()->paginate($perPage, $columns);
    }

    /** @return TModel */
    public function findById(int $id, array $columns = ['*']): Model
    {
        $model = $this->model->newQuery()->find($id, $columns);

        if (! $model) {
            throw new ModelNotFoundException("Model [" . $this->model::class . "] with ID [{$id}] not found.");
        }

        return $model;
    }

    /** @return TModel|null */
    public function findByIdOrNull(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->newQuery()->find($id, $columns);
    }

    /** @return TModel */
    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    /** @return TModel */
    public function update(int $id, array $data): Model
    {
        $model = $this->findById($id);
        $model->update($data);

        return $model->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->findById($id)->delete();
    }

    public function forceDelete(int $id): bool
    {
        $model = $this->model->newQuery()->withTrashed()->findOrFail($id);

        return (bool) $model->forceDelete();
    }

    /** @return TModel|null */
    public function firstWhere(array $conditions): ?Model
    {
        return $this->model->newQuery()->where($conditions)->first();
    }

    /** @return Collection<int, TModel> */
    public function where(array $conditions): Collection
    {
        return $this->model->newQuery()->where($conditions)->get();
    }

    public function exists(array $conditions): bool
    {
        return $this->model->newQuery()->where($conditions)->exists();
    }

    public function count(array $conditions = []): int
    {
        $query = $this->model->newQuery();

        if ($conditions) {
            $query->where($conditions);
        }

        return $query->count();
    }
}
