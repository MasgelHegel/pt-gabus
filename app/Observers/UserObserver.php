<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class UserObserver
{
    public function created(User $user): void
    {
        $this->log('created', $user, [], $user->getAttributes());
    }

    public function updated(User $user): void
    {
        $dirty = $user->getDirty();

        // Never log password changes in audit
        unset($dirty['password'], $dirty['remember_token']);

        if (empty($dirty)) {
            return;
        }

        $old = array_intersect_key($user->getOriginal(), $dirty);

        $this->log('updated', $user, $old, $dirty);
    }

    public function deleted(User $user): void
    {
        $this->log('deleted', $user, $user->getAttributes(), []);
    }

    public function restored(User $user): void
    {
        $this->log('restored', $user, [], $user->getAttributes());
    }

    private function log(string $event, User $user, array $old, array $new): void
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'event'          => $event,
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'old_values'     => $old ?: null,
            'new_values'     => $new ?: null,
            'url'            => Request::fullUrl(),
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
        ]);
    }
}
