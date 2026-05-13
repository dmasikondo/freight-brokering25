<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop the existing blanket unique index
        Schema::table('tender_offers', function (Blueprint $table) {
            $table->dropUnique('unique_active_offer');
        });

        // 2. Add the generated column
        // We use a CASE statement: if active, create a unique string; if not, result is NULL.
        DB::statement("
            ALTER TABLE tender_offers 
            ADD COLUMN active_bidder_slot VARCHAR(255) 
            GENERATED ALWAYS AS (
                CASE 
                    WHEN status IN ('pending', 'shortlisted') 
                    THEN CONCAT(tenderable_type, '-', CAST(tenderable_id AS CHAR), '-', CAST(bidder_id AS CHAR))
                    ELSE NULL 
                END
            ) STORED
        ");

        // 3. Apply the unique constraint to the generated column
        Schema::table('tender_offers', function (Blueprint $table) {
            $table->unique('active_bidder_slot', 'unique_active_offer_slot');
        });
    }

    public function down(): void
    {
        Schema::table('tender_offers', function (Blueprint $table) {
            $table->dropUnique('unique_active_offer_slot');
            $table->dropColumn('active_bidder_slot');
        });

        // Restore the original index
        Schema::table('tender_offers', function (Blueprint $table) {
            $table->unique(
                ['tenderable_type', 'tenderable_id', 'bidder_id'], 
                'unique_active_offer'
            );
        });
    }
};