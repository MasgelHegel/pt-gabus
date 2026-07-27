<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Super admin bypasses all policy checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view-users', 'manage-users'])
            || $user->hasRole(UserRole::Admin->value);
    }

    public function view(User $user, User $target): bool
    {
        if ($target->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        // Users can always view their own profile
        if ($user->id === $target->id) {
            return true;
        }

        return $user->hasAnyPermission(['view-users', 'manage-users'])
            || ($user->hasRole(UserRole::Admin->value) && $target->hasRole(UserRole::Sales->value));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-users')
            || $user->hasRole(UserRole::Admin->value);
    }

    public function update(User $user, User $target): bool
    {
        if ($target->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        // Users can always update their own profile
        if ($user->id === $target->id) {
            return true;
        }

        return $user->hasPermissionTo('manage-users')
            || ($user->hasRole(UserRole::Admin->value) && $target->hasRole(UserRole::Sales->value));
    }

    public function delete(User $user, User $target): bool
    {
        if ($target->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        // Cannot delete yourself
        if ($user->id === $target->id) {
            return false;
        }

        return $user->hasPermissionTo('manage-users')
            || ($user->hasRole(UserRole::Admin->value) && $target->hasRole(UserRole::Sales->value));
    }

    public function restore(User $user, User $target): bool
    {
        if ($target->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->hasPermissionTo('manage-users')
            || ($user->hasRole(UserRole::Admin->value) && $target->hasRole(UserRole::Sales->value));
    }

    public function forceDelete(User $user, User $target): bool
    {
        return $user->hasRole(UserRole::SuperAdmin->value);
    }
}

