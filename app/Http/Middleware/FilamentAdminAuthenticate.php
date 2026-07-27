<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Override Filament Authenticate:
 * - Customer yang sudah login → kick ke portal (bukan admin/login)
 * - Guest & staff → behaviour Filament normal (tampilkan /admin/login)
 */
class FilamentAdminAuthenticate extends FilamentAuthenticate
{
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        $user = $guard->user();

        $panel = Filament::getCurrentOrDefaultPanel();

        if ($user instanceof FilamentUser) {
            if (! $user->canAccessPanel($panel)) {
                // Jika user login tapi tidak boleh akses panel ini (misal customer nyasar ke /admin)
                if ($user->hasRole(UserRole::Customer->value)) {
                    // Redirect customer ke portal customer
                    throw new HttpResponseException(redirect()->route('portal.orders.index'));
                }

                abort(403);
            }
        } else {
            abort_if(config('app.env') !== 'local', 403);
        }
    }

    // Signature tanpa type hint agar cocok dengan parent Filament v4
    protected function redirectTo($request): ?string
    {
        $user = $request->user();

        // User sudah login sebagai customer → tendang ke portal, jangan ke /admin/login
        if ($user && $user->hasRole(UserRole::Customer->value)) {
            return route('portal.orders.index');
        }

        // Guest / belum login → Filament tampilkan /admin/login seperti biasa
        return parent::redirectTo($request);
    }
}
