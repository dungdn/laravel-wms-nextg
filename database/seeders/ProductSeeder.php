<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            [
                'supplier_id' => 1,
                'category_id' => 1,
                'sku' => 'LAP001',
                'name' => 'Dell Latitude 7440',
                'unit' => 'pcs',
                'cost_price' => 18000000,
                'selling_price' => 22000000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => 2,
                'category_id' => 2,
                'sku' => 'MOU001',
                'name' => 'Logitech M650',
                'unit' => 'pcs',
                'cost_price' => 350000,
                'selling_price' => 490000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => 3,
                'category_id' => 4,
                'sku' => 'MON001',
                'name' => 'Samsung ViewFinity S6',
                'unit' => 'pcs',
                'cost_price' => 4500000,
                'selling_price' => 5900000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}