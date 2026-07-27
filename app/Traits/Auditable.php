<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Automatically writes to audit_logs on created / updated / deleted / restored.
 *
 * Usage: add `use Auditable;` to any Eloquent model.
 *
 * To exclude sensitive fields from the log override:
 *   protected array $auditExclude = ['password', 'remember_token'];
 *
 * To include only specific fields override:
 *   protected array $auditInclude = ['status', 'total_amount'];
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => static::writeAudit($model, 'created', [], $model->getAttributes()));
        static::updated(fn (Model $model) => static::writeAudit($model, 'updated', $model->getOriginal(), $model->getChanges()));
        static::deleted(fn (Model $model) => static::writeAudit($model, 'deleted', $model->getAttributes(), []));

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class), true)) {
            static::restored(fn (Model $model) => static::writeAudit($model, 'restored', [], $model->getAttributes()));
        }
    }

    protected static function writeAudit(Model $model, string $event, array $oldValues, array $newValues): void
    {
        // Remove excluded columns
        $exclude = array_merge(
            ['password', 'remember_token', 'updated_at'],
            $model->auditExclude ?? [],
        );

        $filter = fn (array $values) => collect($values)
            ->except($exclude)
            ->when(
                ! empty($model->auditInclude ?? []),
                fn ($c) => $c->only($model->auditInclude)
            )
            ->toArray();

        try {
            AuditLog::create([
                'user_id'        => auth()->id(),
                'event'          => $event,
                'auditable_type' => get_class($model),
                'auditable_id'   => $model->getKey(),
                'old_values'     => $filter($oldValues) ?: null,
                'new_values'     => $filter($newValues) ?: null,
                'url'            => request()->fullUrl(),
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
            ]);
        } catch (\Throwable) {
            // Fail silently — never let audit logging break the main flow
        }
    }
}
