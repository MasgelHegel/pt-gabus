<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\User\CreateUserAction;
use App\Actions\User\DeleteUserAction;
use App\Actions\User\UpdateUserAction;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\UserData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * @extends BaseService<User>
 */
class UserService extends BaseService
{
    public function __construct(
        UserRepositoryInterface $repository,
        private readonly CreateUserAction $createAction,
        private readonly UpdateUserAction $updateAction,
        private readonly DeleteUserAction $deleteAction,
    ) {
        parent::__construct($repository);
    }

    /** @return LengthAwarePaginator<User> */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateWithFilters($filters, $perPage);
    }

    public function findById(int $id): User
    {
        /** @var User */
        return $this->repository->findById($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    public function create(UserData $data): User
    {
        return DB::transaction(fn () => ($this->createAction)($data));
    }

    public function update(int $id, UserData $data): User
    {
        return DB::transaction(fn () => ($this->updateAction)($id, $data));
    }

    public function delete(int $id): bool
    {
        return DB::transaction(fn () => ($this->deleteAction)($id));
    }

    public function updateLastLogin(int $userId, string $ip): void
    {
        $this->repository->updateLastLogin($userId, $ip);
    }
}
