<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->hasRole(UserRole::Customer->value)) {
            return redirect()->route('portal.orders.create');
        }

        return view('portal.auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar, silakan login.',
            'phone.required'     => 'Nomor HP wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Ambil company default (PT Gabus)
        $company = Company::first();

        // Buat user
        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'phone'             => $validated['phone'],
            'password'          => Hash::make($validated['password']),
            'status'            => UserStatus::Active,
            'company_id'        => $company?->id,
            'email_verified_at' => now(),
        ]);

        // Assign role customer
        $user->assignRole(UserRole::Customer->value);

        // Buat record Customer
        Customer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'code'            => 'CUST-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                'name'            => $validated['name'],
                'email'           => $validated['email'],
                'phone'           => $validated['phone'],
                'company_name'    => $validated['name'],
                'credit_limit'    => 0,
                'piutang_balance' => 0,
            ]
        );

        // Auto login
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('portal.orders.create')
            ->with('success', 'Selamat datang, ' . $user->name . '! Akun Anda berhasil dibuat. Silakan mulai pesan gas.');
    }
}
