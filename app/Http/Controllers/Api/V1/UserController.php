<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\UserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\StoreUserRequest;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\UserCollection;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->only(['search', 'status', 'company_id', 'branch_id', 'role', 'sort_by', 'sort_dir']);
        $perPage = (int) $request->input('per_page', 15);

        $users = $this->userService->list($filters, $perPage);

        return ApiResponse::success(new UserCollection($users));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = UserData::fromRequest($request);
        $user = $this->userService->create($data);

        if ($request->input('role')) {
            $user->syncRoles([$request->input('role')]);
        }

        return ApiResponse::created(
            new UserResource($user->load(['roles', 'company', 'branch'])),
            'Pengguna berhasil dibuat'
        );
    }

    public function show(int $user): JsonResponse
    {
        $model = $this->userService->findById($user);

        $this->authorize('view', $model);

        return ApiResponse::success(
            new UserResource($model->load(['roles', 'permissions', 'company', 'branch']))
        );
    }

    public function update(UpdateUserRequest $request, int $user): JsonResponse
    {
        $data  = UserData::fromRequest($request);
        $model = $this->userService->update($user, $data);

        if ($request->has('role')) {
            $model->syncRoles([$request->input('role')]);
        }

        return ApiResponse::success(
            new UserResource($model->load(['roles', 'company', 'branch'])),
            'Pengguna berhasil diperbarui'
        );
    }

    public function destroy(int $user): JsonResponse
    {
        $model = $this->userService->findById($user);

        $this->authorize('delete', $model);

        $this->userService->delete($user);

        return ApiResponse::success(null, 'Pengguna berhasil dihapus');
    }
}
