<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Contracts\Repositories\UserRepositoryInterface;

class DeleteUserAction
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
