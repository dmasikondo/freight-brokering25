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
        Schema::create('fleets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->integer('horses');
			$table->integer('trailer_qty');
			$table->boolean('online')->default(0);		
            $table->timestamps();
        });  
        
        Schema::create('fleet_trailer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_id')->constrained()->onDelete('cascade');
            $table->foreignId('trailer_id')->constrained()->onDelete('cascade');
            $table->primary(['fleet_id', 'trailer_id']);    
            $table->timestamps();
        });	        
            
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleets');
        Schema::dropIfExists('fleet_trailer');
    }
};
