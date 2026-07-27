<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\InvoiceRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        InvoiceRepositoryInterface::class => InvoiceRepository::class,
        PaymentRepositoryInterface::class => PaymentRepository::class,
        CustomerRepositoryInterface::class => CustomerRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
