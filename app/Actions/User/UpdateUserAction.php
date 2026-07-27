<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\UserData;
use App\Events\UserUpdated;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(int $id, UserData $data): User
    {
        $payload = $data->toArray();

        // Only hash if password is explicitly provided
        if (! empty($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        } else {
            unset($payload['password']);
        }

        if (auth()->check()) {
            $payload['updated_by'] = auth()->id();
        }

        /** @var User $user */
        $user = $this->repository->update($id, $payload);

        event(new UserUpdated($user));

        return $user;
    }
}
