<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class GasProductSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan kategori Gas ada
        $category = Category::firstOrCreate(
            ['name' => 'Gas LPG'],
            [
                'slug'        => 'gas-lpg',
                'description' => 'Produk Gas LPG Berbagai Ukuran',
            ]
        );

        // Tabung 12 Kg — harga rata-rata dari spreadsheet
        Product::firstOrCreate(
            ['sku' => 'GAS-12KG'],
            [
                'category_id' => $category->id,
                'name'        => 'Gas LPG 12 Kg',
                'unit'        => 'Tabung',
                'buy_price'   => 170000,
                'sell_price'  => 215000,
                'stock'       => 9999,
                'min_stock'   => 10,
                'description' => 'Tabung Gas LPG 12 Kilogram',
            ]
        );

        // Tabung 50 Kg — harga rata-rata dari spreadsheet
        Product::firstOrCreate(
            ['sku' => 'GAS-50KG'],
            [
                'category_id' => $category->id,
                'name'        => 'Gas LPG 50 Kg',
                'unit'        => 'Tabung',
                'buy_price'   => 700000,
                'sell_price'  => 850000,
                'stock'       => 9999,
                'min_stock'   => 5,
                'description' => 'Tabung Gas LPG 50 Kilogram',
            ]
        );

        $this->command->info('Gas products seeded: Gas LPG 12 Kg & Gas LPG 50 Kg');
    }
}
