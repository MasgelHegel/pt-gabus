<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class BaseService
{
    /**
     * @param BaseRepositoryInterface<TModel> $repository
     */
    public function __construct(
        protected readonly BaseRepositoryInterface $repository,
    ) {}
}
