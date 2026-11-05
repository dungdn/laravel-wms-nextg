<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        Warehouse::upsert(
            [
                [
                    'code' => 'WH-HCM',
                    'name' => 'Ho Chi Minh Central Warehouse',
                    'address' => 'District 9, Ho Chi Minh City',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'WH-HN',
                    'name' => 'Ha Noi Northern Warehouse',
                    'address' => 'Long Bien District, Ha Noi',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'WH-DN',
                    'name' => 'Da Nang Central Warehouse',
                    'address' => 'Hai Chau District, Da Nang',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['code'],
            [
                'name',
                'address',
                'is_active',
                'updated_at',
            ]
        );
    }
}
