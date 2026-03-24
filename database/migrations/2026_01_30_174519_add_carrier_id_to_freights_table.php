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
        Schema::table('freights', function (Blueprint $table) {
                       $table->foreignId('shipper_id')
                ->after('id')
                ->nullable() // Nullable initially since we have existing data
                ->constrained('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('freights', function (Blueprint $table) {
            $table->dropColumn('shipper_id');
        });
    }
};
