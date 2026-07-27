<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ----------------------------------------------------------------
        // Define all permissions per module
        // ----------------------------------------------------------------
        $permissions = [
            // Dashboard
            'view-dashboard',

            // Users & Roles
            'view-users',    'manage-users',
            'view-roles',    'manage-roles',
            'view-permissions', 'manage-permissions',

            // Customers
            'view-customers',  'manage-customers',

            // Suppliers
            'view-suppliers',  'manage-suppliers',

            // Products
            'view-products',   'manage-products',

            // Categories
            'view-categories', 'manage-categories',

            // Warehouses & Stock
            'view-warehouses', 'manage-warehouses',
            'view-stock',      'manage-stock',

            // Orders (Customer Orders)
            'view-orders',     'manage-orders',
            'approve-orders',

            // Sales Orders
            'view-sales-orders',    'manage-sales-orders',

            // Purchase Orders
            'view-purchase-orders', 'manage-purchase-orders',

            // Invoices
            'view-invoices',   'manage-invoices',

            // Payments
            'view-payments',   'manage-payments',
            'verify-payments',

            // Shipments
            'view-shipments',  'manage-shipments',

            // Accounting / Journals
            'view-journals',   'manage-journals',
            'view-accounts',   'manage-accounts',

            // Reports
            'view-reports',    'export-reports',

            // Activity Log
            'view-activity-logs',

            // Settings
            'manage-settings',

            // Backup & Monitoring
            'access-horizon',
            'access-backup',

            // Customer Portal
            'portal-access',
            'portal-create-order',
            'portal-upload-payment',
            'portal-view-invoice',
            'portal-view-order',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ----------------------------------------------------------------
        // Super Admin — full access
        // ----------------------------------------------------------------
        $superAdmin = Role::firstOrCreate(['name' => UserRole::SuperAdmin->value, 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        // ----------------------------------------------------------------
        // Admin — operational full access, no system/backup
        // ----------------------------------------------------------------
        $adminPerms = [
            'view-dashboard',
            'view-customers',   'manage-customers',
            'view-suppliers',   'manage-suppliers',
            'view-products',    'manage-products',
            'view-categories',  'manage-categories',
            'view-warehouses',  'manage-warehouses',
            'view-stock',       'manage-stock',
            'view-orders',      'manage-orders',    'approve-orders',
            'view-sales-orders', 'manage-sales-orders',
            'view-purchase-orders', 'manage-purchase-orders',
            'view-invoices',    'manage-invoices',
            'view-payments',    'manage-payments',  'verify-payments',
            'view-shipments',   'manage-shipments',
            'view-journals',    'manage-journals',
            'view-accounts',    'manage-accounts',
            'view-reports',     'export-reports',
            'view-activity-logs',
        ];

        $admin = Role::firstOrCreate(['name' => UserRole::Admin->value, 'guard_name' => 'web']);
        $admin->syncPermissions($adminPerms);

        // ----------------------------------------------------------------
        // Sales — order + customer + product + stock + invoice view
        // ----------------------------------------------------------------
        $salesPerms = [
            'view-dashboard',
            'view-customers',   'manage-customers',
            'view-products',    'manage-products',
            'view-categories',
            'view-stock',
            'view-orders',      'manage-orders',
            'view-sales-orders', 'manage-sales-orders',
            'view-invoices',
            'view-shipments',   'manage-shipments',
        ];

        $sales = Role::firstOrCreate(['name' => UserRole::Sales->value ?? 'sales', 'guard_name' => 'web']);
        $sales->syncPermissions($salesPerms);

        // ----------------------------------------------------------------
        // Customer — portal only
        // ----------------------------------------------------------------
        $customerPerms = [
            'portal-access',
            'portal-create-order',
            'portal-upload-payment',
            'portal-view-invoice',
            'portal-view-order',
        ];

        $customer = Role::firstOrCreate(['name' => UserRole::Customer->value ?? 'customer', 'guard_name' => 'web']);
        $customer->syncPermissions($customerPerms);

        $this->command->info('Roles & permissions seeded: super_admin, admin, sales, customer');
    }
}
