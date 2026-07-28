<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['name' => 'PT Gabus Digital Indonesia'],
            [
                'legal_name'  => 'PT Gabus Digital Indonesia',
                'phone'       => '+62-21-1234567',
                'email'       => 'info@gabus.test',
                'address'     => 'Jl. Sudirman No. 1',
                'city'        => 'Jakarta Pusat',
                'province'    => 'DKI Jakarta',
                'postal_code' => '10220',
                'country'     => 'Indonesia',
                'is_active'   => true,
            ]
        );

        $branch = Branch::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'HQ'],
            [
                'name'           => 'Kantor Pusat',
                'phone'          => '+62-21-1234567',
                'email'          => 'hq@gabus.test',
                'address'        => 'Jl. Sudirman No. 1',
                'city'           => 'Jakarta Pusat',
                'province'       => 'DKI Jakarta',
                'is_headquarter' => true,
                'is_active'      => true,
            ]
        );

        // 1. Super Admin
        $superAdmin = User::where('email', 'superadmin@gabus.test')->first();
        if ($superAdmin) {
            $superAdmin->update([
                'email' => 'firlanarizka88@gmail.com',
                'name' => 'Rizka Firlana',
                'password' => Hash::make('bosrizka123'),
            ]);
        } else {
            $superAdmin = User::updateOrCreate(
                ['email' => 'firlanarizka88@gmail.com'],
                [
                    'name'              => 'Rizka Firlana',
                    'password'          => Hash::make('bosrizka123'),
                    'phone'             => '+62-812-0000-0001',
                    'status'            => UserStatus::Active,
                    'company_id'        => $company->id,
                    'branch_id'         => $branch->id,
                    'email_verified_at' => now(),
                ]
            );
        }
        $superAdmin->syncRoles([UserRole::SuperAdmin->value]);

        // 2. Admin
        $admin = User::where('email', 'admin@gabus.test')->first();
        if ($admin) {
            $admin->update([
                'email' => 'bhoypratama71@gmail.com',
                'name' => 'Bhoy Rafi Pratama',
                'password' => Hash::make('boyadmin123'),
            ]);
        } else {
            $admin = User::updateOrCreate(
                ['email' => 'bhoypratama71@gmail.com'],
                [
                    'name'              => 'Bhoy Rafi Pratama',
                    'password'          => Hash::make('boyadmin123'),
                    'phone'             => '+62-812-0000-0002',
                    'status'            => UserStatus::Active,
                    'company_id'        => $company->id,
                    'branch_id'         => $branch->id,
                    'email_verified_at' => now(),
                ]
            );
        }
        $admin->syncRoles([UserRole::Admin->value]);

        // 3. Sales
        $sales = User::withTrashed()->firstOrCreate(
            ['email' => 'sales@gabus.test'],
            [
                'name'              => 'Budi Sales Executive',
                'password'          => Hash::make('password'),
                'phone'             => '+62-812-0000-0003',
                'status'            => UserStatus::Active,
                'company_id'        => $company->id,
                'branch_id'         => $branch->id,
                'email_verified_at' => now(),
            ]
        );
        $sales->syncRoles([UserRole::Sales->value]);

        // 4. Accounting
        $accounting = User::withTrashed()->firstOrCreate(
            ['email' => 'accounting@gabus.test'],
            [
                'name'              => 'Accounting Staff',
                'password'          => Hash::make('password'),
                'phone'             => '+62-812-0000-0005',
                'status'            => UserStatus::Active,
                'company_id'        => $company->id,
                'branch_id'         => $branch->id,
                'email_verified_at' => now(),
            ]
        );
        $accounting->syncRoles([UserRole::Accounting->value]);

        // 5. Customer User
        $customerUser = User::withTrashed()->firstOrCreate(
            ['email' => 'customer@gabus.test'],
            [
                'name'              => 'Customer Demo',
                'password'          => Hash::make('password'),
                'phone'             => '+62-812-0000-0004',
                'status'            => UserStatus::Active,
                'company_id'        => $company->id,
                'branch_id'         => $branch->id,
                'email_verified_at' => now(),
            ]
        );
        $customerUser->syncRoles([UserRole::Customer->value]);

        Customer::firstOrCreate(
            ['user_id' => $customerUser->id],
            [
                'code'            => 'CUST-001',
                'name'            => 'Customer Demo',
                'company_name'    => 'PT Mitra Sejahtera',
                'email'           => 'customer@gabus.test',
                'phone'           => '+62-812-0000-0004',
                'address'         => 'Jl. Gatot Subroto No. 45, Jakarta',
                'credit_limit'    => 50000000,
                'piutang_balance' => 0,
            ]
        );

        $this->command->info('Users seeded: superadmin@gabus.test, admin@gabus.test, sales@gabus.test, accounting@gabus.test, customer@gabus.test');
    }
}
