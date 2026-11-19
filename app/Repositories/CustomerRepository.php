<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

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
            ->update([
                // CASE (bukan GREATEST) agar portable antara MySQL dan SQLite
                'piutang_balance' => DB::raw("CASE WHEN piutang_balance - {$amount} > 0 THEN piutang_balance - {$amount} ELSE 0 END"),
            ]);
    }
}
