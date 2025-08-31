<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, clear existing data to prevent duplicates on re-seeding
        DB::table('countries')->insert([
            ['name'=>'zimbabwe'],
            [ 'name'=>'south africa'],
        ]);
    }
}
