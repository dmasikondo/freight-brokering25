<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrailerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $trailers = [
            'Air Ride Van',
            'Dry Bulk',
            'Dump',
            'Car Carrying',
            'Curtain Side',
            'Double Decker',
            'Drop Decker',
            'Flat Bed',
            'Hopper Bottom',
            'Live Bottom',
            'Livestock',
            'Low Boy',
            'Power Only',
            'Reefer',
            'Removable Gooseneck',
            'Slide',
            'Step Deck',
        ];

        $now = now();
        $data = array_map(function ($name) use ($now) {
            return [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $trailers);

        DB::table('trailers')->insert($data);
    }
}
