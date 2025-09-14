<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $goods = [
            'boats',
            'business and industrial goods',
            'food and agricultural goods',
            'FTL freight',
            'hazardous goods',
            'heavy equipment',
            'horses and livestock',
            'household goods',
            'household and office moves',
            'LTL freight',
            'motor cycles and power sports',
            'pets',
            'special care items',
            'vehicles',
        ];

        foreach ($goods as $good) {
            DB::table('goods')->insert([
                'name' => $good,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
