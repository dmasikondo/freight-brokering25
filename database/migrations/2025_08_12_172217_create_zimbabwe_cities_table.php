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
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('zimbabwe_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('abbreviation', 3)->unique();
            $table->foreignId('province_id')->constrained('provinces');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zimbabwe_cities');
        Schema::dropIfExists('provinces');
    }
};
