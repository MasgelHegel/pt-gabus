<?php

declare(strict_types=1);

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $orderPermissions = [
            'view-orders',
            'manage-orders',
            'approve-orders',
        ];

        foreach ($orderPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Grant order permissions to Sales role
        $salesRole = Role::where('name', UserRole::Sales->value)->first();
        if ($salesRole) {
            $salesRole->givePermissionTo($orderPermissions);
        }

        // Grant order permissions to Accounting role
        $accountingRole = Role::where('name', UserRole::Accounting->value)->first();
        if ($accountingRole) {
            $accountingRole->givePermissionTo($orderPermissions);
        }

        // Grant to Super Admin role as well
        $superAdminRole = Role::where('name', UserRole::SuperAdmin->value)->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($orderPermissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve permissions
    }
};
