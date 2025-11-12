<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Freight;
use Illuminate\Database\Seeder;

class FreightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create the primary user who will own all the freights (the 'creator')
        $creator = User::whereEmail('dmasikondo@gmail.com')->firstOrFail();

        // 2. Create a separate user to act as the 'publisher' for published freights
       // $publisher = $creator;

        // --- Create a mix of freights for the 'Shipper Test User' ---
        
        // A. 10 PUBLISHED Freights
        // These require both creator_id and publisher_id
        Freight::factory(['creator_id' =>$creator->id])
            ->count(10)
            ->published()
            ->create();

        // B. 5 DRAFT Freights
        // These only need the creator_id
        Freight::factory(['creator_id' =>$creator->id])
            ->count(10)
            ->draft()
            ->create();

        // C. 5 EXPIRED Freights
        // These need creator_id and are marked as expired
        Freight::factory(['creator_id' =>$creator->id])
            ->count(10)
            ->expired()
            ->create();

        $this->command->info('20 freights successfully seeded for the user: shipper@example.com');
    }
}