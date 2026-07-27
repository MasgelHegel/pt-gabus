<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\UserData;
use App\Events\UserCreated;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(UserData $data): User
    {
        $payload = $data->toArray();

        if (! empty($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        }

        // Track creator
        if (auth()->check()) {
            $payload['created_by'] = auth()->id();
            $payload['updated_by'] = auth()->id();
        }

        /** @var User $user */
        $user = $this->repository->create($payload);

        event(new UserCreated($user));

        return $user;
    }
}
