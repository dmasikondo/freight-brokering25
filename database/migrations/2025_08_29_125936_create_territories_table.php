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
        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Unique name for the territory
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        }); 
        
        Schema::create('country_territory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('territory_id')->constrained('territories')->onDelete('cascade');
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['territory_id', 'country_id']);
        });        

        Schema::create('province_territory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('territory_id')->constrained('territories')->onDelete('cascade');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['territory_id', 'province_id']);
        }); 

        Schema::create('territory_zimbabwe_city', function (Blueprint $table) {
            $table->id();
            $table->foreignId('territory_id')->constrained('territories')->onDelete('cascade');
            $table->foreignId('zimbabwe_city_id')->constrained('zimbabwe_cities')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['territory_id', 'zimbabwe_city_id']);
        }); 
        
        Schema::create('territory_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('territory_id')->constrained('territories')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by_user_id')->constrained('users')->onDelete('cascade'); // Admin who assigned the territory
            $table->timestamps();

            $table->unique(['territory_id', 'user_id']); // Ensure unique assignments
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('territory_user');
        Schema::dropIfExists('territory_zimbabwe_city');
        Schema::dropIfExists('country_territory');
        Schema::dropIfExists('province_territory');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('territories');
    }
};
