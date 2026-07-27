<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        // Kalau sudah login, arahkan ke tempat yang tepat
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole(UserRole::Customer->value)) {
                return redirect()->route('portal.orders.create');
            }
            // Staff/admin yang buka /portal/login → ke admin
            return redirect('/admin');
        }

        return view('portal.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi admin.']);
        }

        $request->session()->regenerate();

        if ($user->hasRole(UserRole::Customer->value)) {
            return redirect()->intended(route('portal.orders.create'));
        }

        return redirect()->intended('/admin');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
