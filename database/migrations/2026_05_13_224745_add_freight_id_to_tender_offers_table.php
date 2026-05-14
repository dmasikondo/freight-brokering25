<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tender_offers', function (Blueprint $table) {
            $table->foreignId('freight_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->after('bidder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tender_offers', function (Blueprint $table) {
            $table->dropColumn('freight_id');
        });
    }
};
