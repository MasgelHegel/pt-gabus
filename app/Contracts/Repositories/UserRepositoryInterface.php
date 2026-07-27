<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepositoryInterface<User>
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    /** @return LengthAwarePaginator<User> */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function updateLastLogin(int $userId, string $ip): void;
}
