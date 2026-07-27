<?php

declare(strict_types=1);

use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\PortalAuthController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────────────────────────────────────
// Root — halaman pemilih role
// ──────────────────────────────────────────────────────────────────────────────
Route::get('/', fn () => view('welcome'))->name('home');

// ──────────────────────────────────────────────────────────────────────────────
// Public Order Gas — tanpa login
// ──────────────────────────────────────────────────────────────────────────────
Route::get('/order', [PublicOrderController::class, 'create'])->name('order.create');
Route::post('/order', [PublicOrderController::class, 'store'])->name('order.store');
Route::get('/order/sukses', [PublicOrderController::class, 'success'])->name('order.success');

// ──────────────────────────────────────────────────────────────────────────────
// Register Customer
// ──────────────────────────────────────────────────────────────────────────────
Route::get('/register', [RegisterController::class, 'show'])
    ->name('register');
Route::post('/register', [RegisterController::class, 'store'])
    ->name('register.store');

// ──────────────────────────────────────────────────────────────────────────────
// Customer Portal — Auth
// ──────────────────────────────────────────────────────────────────────────────

// Named 'login' route → dipakai middleware auth Laravel secara default
// Customer diarahkan ke portal login, bukan Filament login
Route::get('/login', fn () => redirect()->route('portal.login'))->name('login');

Route::get('/portal/login', [PortalAuthController::class, 'showLogin'])
    ->name('portal.login');

Route::post('/portal/login', [PortalAuthController::class, 'login'])
    ->name('portal.login.post');

Route::post('/portal/logout', [PortalAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('portal.logout');

Route::post('/logout', [PortalAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ──────────────────────────────────────────────────────────────────────────────
// Customer Portal — Public
// ──────────────────────────────────────────────────────────────────────────────
Route::get('/portal', [CustomerPortalController::class, 'catalog'])
    ->name('portal.catalog');

// ──────────────────────────────────────────────────────────────────────────────
// Customer Portal — Protected (hanya customer)
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', \App\Http\Middleware\EnsureIsCustomer::class])
    ->prefix('portal')
    ->name('portal.')
    ->group(function (): void {

        Route::get('/orders', [CustomerPortalController::class, 'orders'])
            ->name('orders.index');

        Route::get('/orders/create', [CustomerPortalController::class, 'createOrder'])
            ->name('orders.create');

        Route::post('/orders', [CustomerPortalController::class, 'storeOrder'])
            ->name('orders.store');

        Route::get('/invoices', [CustomerPortalController::class, 'invoices'])
            ->name('invoices.index');

        Route::post('/invoices/{invoice}/upload-payment', [CustomerPortalController::class, 'uploadPayment'])
            ->name('invoices.upload-payment');

        Route::post('/shipments/{shipment}/confirm', [CustomerPortalController::class, 'confirmDelivery'])
            ->name('shipments.confirm');
    });
