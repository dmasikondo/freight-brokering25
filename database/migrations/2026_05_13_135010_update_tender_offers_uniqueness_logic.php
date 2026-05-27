<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop the old unconstrained unique index safely
        try {
            Schema::table('tender_offers', function (Blueprint $table) {
                $table->dropUnique('unique_active_offer');
            });
        } catch (\Exception $e) {
            // Index might already be gone/renamed from the previous column rename
        }

        // 2. Add the VIRTUAL column and the unique index
        Schema::table('tender_offers', function (Blueprint $table) {
            $table->string('active_bidder_slot', 255)
                ->virtualAs("
                    CASE 
                        WHEN status IN ('pending', 'shortlisted') 
                        THEN CONCAT(tenderable_type, '-', CAST(tenderable_id AS CHAR), '-', CAST(bidder_id AS CHAR))
                        ELSE NULL 
                    END
                ")
                ->nullable()
                ->after('status');
                
            $table->unique('active_bidder_slot', 'unique_active_offer_slot');
        });
    }

    public function down(): void
    {
        Schema::table('tender_offers', function (Blueprint $table) {
            $table->dropUnique('unique_active_offer_slot');
            $table->dropColumn('active_bidder_slot');
        });

        Schema::table('tender_offers', function (Blueprint $table) {
            $table->unique(
                ['tenderable_type', 'tenderable_id', 'bidder_id'], 
                'unique_active_offer'
            );
        });
    }
};