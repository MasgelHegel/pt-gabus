<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Strict model behavior in development
        Model::shouldBeStrict(! app()->isProduction());

        // Disable wrapping of single resource
        JsonResource::withoutWrapping();

        // Force HTTPS in production
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // Default password rules
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->uncompromised();
        });

        // Register model observers
        // UserObserver: audit log setiap perubahan user
        User::observe(UserObserver::class);

        // CATATAN: InvoiceObserver, SalesOrderObserver, PaymentObserver
        // TIDAK diaktifkan karena logic sudah ditangani oleh OrderWorkflowService
        // untuk menghindari double journal/invoice/piutang.
        // GoodsReceiptObserver juga tidak aktif — stok ditangani verifyQCCheck().

        // Register policies
        Gate::policy(User::class, UserPolicy::class);

        // Super admin bypasses all authorization checks
        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
