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
        Schema::table('lanes', function (Blueprint $table) {
            // Adds capacity_unit (e.g., 'tonnes' or 'litres')
            $table->string('capacity_unit')->nullable()->after('capacity');

            // Adds rate_type (e.g., 'per_km' or 'flat_rate')
            $table->string('rate_type')->nullable()->after('rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lanes', function (Blueprint $table) {
            $table->dropColumn(['capacity_unit', 'rate_type']);
        });
    }
};
