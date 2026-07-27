<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\Portal\PortalDashboardController;
use App\Http\Controllers\Api\V1\Portal\PortalInvoiceController;
use App\Http\Controllers\Api\V1\Portal\PortalOrderController;
use App\Http\Controllers\Api\V1\Portal\PortalProductController;
use App\Http\Controllers\Api\V1\Portal\PortalShipmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {

    // -------------------------------------------------------------------------
    // Public routes
    // -------------------------------------------------------------------------
    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])->name('login');
    });

    // Public product catalog (no auth required)
    Route::prefix('products')->name('products.')->group(function (): void {
        Route::get('/', [PortalProductController::class, 'index'])->name('index');
        Route::get('{id}', [PortalProductController::class, 'show'])->name('show');
    });

    // -------------------------------------------------------------------------
    // Authenticated routes (Sanctum)
    // -------------------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function (): void {

        // Auth
        Route::prefix('auth')->name('auth.')->group(function (): void {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        });

        // Users (admin)
        Route::apiResource('users', UserController::class);

        // -------------------------------------------------------------------------
        // Customer Portal API routes
        // -------------------------------------------------------------------------
        Route::prefix('portal')->name('portal.')->group(function (): void {

            // Dashboard
            Route::get('dashboard', PortalDashboardController::class)->name('dashboard');

            // Orders
            Route::prefix('orders')->name('orders.')->group(function (): void {
                Route::get('/', [PortalOrderController::class, 'index'])->name('index');
                Route::post('/', [PortalOrderController::class, 'store'])->name('store');
                Route::get('{id}', [PortalOrderController::class, 'show'])->name('show');
            });

            // Shipments
            Route::prefix('shipments')->name('shipments.')->group(function (): void {
                Route::post('{id}/confirm', [PortalShipmentController::class, 'confirm'])->name('confirm');
            });

            // Invoices & Payments
            Route::prefix('invoices')->name('invoices.')->group(function (): void {
                Route::get('/', [PortalInvoiceController::class, 'index'])->name('index');
                Route::get('{id}', [PortalInvoiceController::class, 'show'])->name('show');
                Route::post('{id}/upload-payment', [PortalInvoiceController::class, 'uploadPayment'])->name('upload-payment');
            });
        });
    });
});
