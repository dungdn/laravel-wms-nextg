<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::insert([
            [
                'code' => 'SUP001',
                'name' => 'Dell Vietnam',
                'phone' => '0900000001',
                'email' => 'sales@dell.vn',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SUP002',
                'name' => 'Logitech Vietnam',
                'phone' => '0900000002',
                'email' => 'sales@logitech.vn',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SUP003',
                'name' => 'Samsung Vietnam',
                'phone' => '0900000003',
                'email' => 'sales@samsung.vn',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}