<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $dell = Supplier::where('code', 'SUP001')->firstOrFail();

        $laptop = Category::where('code', 'CAT001')->firstOrFail();

        Product::upsert(
            [
                [
                    'supplier_id' => $dell->id,
                    'category_id' => $laptop->id,
                    'sku' => 'LAP001',
                    'name' => 'Dell Latitude 7440',
                    'unit' => 'pcs',
                    'cost_price' => 18000000,
                    'selling_price' => 22000000,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'supplier_id' => $dell->id,
                    'category_id' => $laptop->id,
                    'sku' => 'MOU001',
                    'name' => 'Logitech M650',
                    'unit' => 'pcs',
                    'cost_price' => 350000,
                    'selling_price' => 490000,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'supplier_id' => $dell->id,
                    'category_id' => $laptop->id,
                    'sku' => 'MON001',
                    'name' => 'Samsung ViewFinity S6',
                    'unit' => 'pcs',
                    'cost_price' => 4500000,
                    'selling_price' => 5900000,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['sku'],
            [
                'supplier_id',
                'category_id',
                'name',
                'unit',
                'cost_price',
                'selling_price',
                'is_active',
                'updated_at',
            ]
        );
    }
}
