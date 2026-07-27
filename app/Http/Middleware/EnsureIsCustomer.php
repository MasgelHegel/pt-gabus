<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan user yang mengakses portal adalah customer.
 * Jika staff/admin nyasar ke /portal, arahkan ke /admin.
 */
class EnsureIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('portal.login');
        }

        // Staff/admin yang nyasar ke portal → kirim ke admin panel
        if (! $user->hasRole(UserRole::Customer->value)) {
            return redirect('/admin');
        }

        // Customer tidak aktif
        if (! $user->isActive()) {
            auth()->logout();
            return redirect()->route('portal.login')
                ->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi admin.']);
        }

        return $next($request);
    }
}
