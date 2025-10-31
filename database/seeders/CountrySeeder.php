<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, clear existing data to prevent duplicates on re-seeding
        DB::table('countries')->truncate(); // Safer for testing/seeding

        $now = Carbon::now();

        DB::table('countries')->insert([
            [
                'name' => 'zimbabwe',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'south africa',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
