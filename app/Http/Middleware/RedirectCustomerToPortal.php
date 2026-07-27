<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blokir customer dari semua halaman /admin (termasuk login page).
 * - Customer yang sudah login → redirect ke portal
 * - Customer yang belum login coba akses /admin → redirect ke /portal/login
 */
class RedirectCustomerToPortal
{
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya berlaku untuk request ke /admin*
        if (! $request->is('admin*')) {
            return $next($request);
        }

        $user = $request->user();

        // User sudah login sebagai customer → tendang ke portal
        if ($user && $user->hasRole(UserRole::Customer->value)) {
            return redirect()->route('portal.orders.create')
                ->with('info', 'Gunakan Portal Customer untuk mengakses layanan.');
        }

        return $next($request);
    }
}
