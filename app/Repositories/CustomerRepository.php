<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;

/** @extends BaseRepository<Customer> */
class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function findByUserId(int $userId): ?Customer
    {
        /** @var Customer|null */
        return $this->model->newQuery()->where('user_id', $userId)->first();
    }

    public function findByCode(string $code): ?Customer
    {
        /** @var Customer|null */
        return $this->model->newQuery()->where('code', $code)->first();
    }

    public function incrementPiutang(int $customerId, float $amount): void
    {
        $this->model->newQuery()
            ->where('id', $customerId)
            ->increment('piutang_balance', $amount);
    }

    public function decrementPiutang(int $customerId, float $amount): void
    {
        $this->model->newQuery()
            ->where('id', $customerId)
            ->decrement('piutang_balance', $amount);
    }
}
