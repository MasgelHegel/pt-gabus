<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Warehouses
        $wh = Warehouse::firstOrCreate(['code' => 'GD-01'], [
            'name'    => 'Gudang Utama',
            'address' => 'Jl. Industri No. 1, Jakarta',
        ]);

        // Categories
        $cats = [
            ['name' => 'Elektronik',    'slug' => 'elektronik'],
            ['name' => 'Aksesoris',     'slug' => 'aksesoris'],
            ['name' => 'Peralatan',     'slug' => 'peralatan'],
            ['name' => 'Bahan Baku',    'slug' => 'bahan-baku'],
        ];
        $catMap = [];
        foreach ($cats as $c) {
            $catMap[$c['slug']] = Category::firstOrCreate(['slug' => $c['slug']], $c)->id;
        }

        // Suppliers
        $supplier = Supplier::firstOrCreate(['code' => 'SUP-001'], [
            'name'         => 'PT Supplier Utama',
            'company_name' => 'PT Supplier Utama',
            'email'        => 'supplier@example.com',
            'phone'        => '+62-21-9999999',
            'address'      => 'Jl. Supplier No. 1',
        ]);

        // Products
        $products = [
            ['sku'=>'PRD-001','name'=>'Laptop Bisnis 14"',   'category'=>'elektronik', 'buy_price'=>8000000, 'sell_price'=>10000000,'stock'=>25,'min_stock'=>5],
            ['sku'=>'PRD-002','name'=>'Mouse Wireless',       'category'=>'aksesoris',  'buy_price'=>150000,  'sell_price'=>200000,  'stock'=>100,'min_stock'=>10],
            ['sku'=>'PRD-003','name'=>'Keyboard Mekanik',     'category'=>'aksesoris',  'buy_price'=>500000,  'sell_price'=>700000,  'stock'=>50, 'min_stock'=>8],
            ['sku'=>'PRD-004','name'=>'Monitor 24" FHD',      'category'=>'elektronik', 'buy_price'=>2000000, 'sell_price'=>2500000, 'stock'=>15, 'min_stock'=>3],
            ['sku'=>'PRD-005','name'=>'Headset Gaming',       'category'=>'aksesoris',  'buy_price'=>300000,  'sell_price'=>450000,  'stock'=>3,  'min_stock'=>5],
            ['sku'=>'PRD-006','name'=>'Flash Drive 64GB',     'category'=>'aksesoris',  'buy_price'=>80000,   'sell_price'=>120000,  'stock'=>200,'min_stock'=>20],
        ];

        foreach ($products as $p) {
            $catId = $catMap[$p['category']] ?? null;
            Product::firstOrCreate(['sku' => $p['sku']], [
                'category_id' => $catId,
                'sku'         => $p['sku'],
                'name'        => $p['name'],
                'buy_price'   => $p['buy_price'],
                'sell_price'  => $p['sell_price'],
                'stock'       => $p['stock'],
                'min_stock'   => $p['min_stock'],
                'unit'        => 'pcs',
                'description' => 'Produk ' . $p['name'],
            ]);
        }

        // Create customer user & link
        $customerUser = User::firstOrCreate(['email' => 'customer@gabus.test'], [
            'name'              => 'Customer Demo',
            'password'          => \Illuminate\Support\Facades\Hash::make('password'),
            'email_verified_at' => now(),
            'status'            => \App\Enums\UserStatus::Active,
        ]);
        $customerUser->syncRoles([\App\Enums\UserRole::Customer->value]);

        Customer::firstOrCreate(['code' => 'CST-001'], [
            'user_id'      => $customerUser->id,
            'name'         => 'Customer Demo',
            'company_name' => 'CV Demo Customer',
            'email'        => 'customer@gabus.test',
            'phone'        => '+62-812-1111-2222',
            'address'      => 'Jl. Customer No. 1, Jakarta',
            'credit_limit' => 50000000,
        ]);

        // Create sales user
        $salesUser = User::firstOrCreate(['email' => 'sales@gabus.test'], [
            'name'              => 'Sales Demo',
            'password'          => \Illuminate\Support\Facades\Hash::make('password'),
            'email_verified_at' => now(),
            'status'            => \App\Enums\UserStatus::Active,
        ]);
        $salesUser->syncRoles([\App\Enums\UserRole::Sales->value]);

        $this->command->info('Master data seeded: categories, products, warehouse, supplier, customer, sales user.');
        $this->command->info('  Customer:  customer@gabus.test / password');
        $this->command->info('  Sales:     sales@gabus.test / password');
    }
}
