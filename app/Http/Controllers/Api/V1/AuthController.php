<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user = Auth::user();

        // Revoke existing tokens for device if device_name specified
        $deviceName = $request->input('device_name', $request->userAgent() ?? 'api');

        $token = $user->createToken($deviceName, ['*'], now()->addDays(30));

        $this->userService->updateLastLogin($user->id, $request->ip());

        return ApiResponse::success([
            'access_token' => $token->plainTextToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $token->accessToken->expires_at?->toISOString(),
            'user'         => new UserResource($user->load(['roles', 'permissions', 'company', 'branch'])),
        ], 'Login berhasil');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logout berhasil');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(null, 'Semua sesi berhasil dihapus');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'permissions', 'company', 'branch']);

        return ApiResponse::success(new UserResource($user));
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();
        $deviceName = $currentToken->name;

        $currentToken->delete();

        $token = $user->createToken($deviceName, ['*'], now()->addDays(30));

        return ApiResponse::success([
            'access_token' => $token->plainTextToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $token->accessToken->expires_at?->toISOString(),
        ], 'Token diperbarui');
    }
}
