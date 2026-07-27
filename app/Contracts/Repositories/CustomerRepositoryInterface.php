<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Customer;

/**
 * @extends BaseRepositoryInterface<Customer>
 */
interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserId(int $userId): ?Customer;
    public function findByCode(string $code): ?Customer;
    public function incrementPiutang(int $customerId, float $amount): void;
    public function decrementPiutang(int $customerId, float $amount): void;
}
