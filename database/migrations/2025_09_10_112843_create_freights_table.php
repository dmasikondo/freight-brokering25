<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\FreightStatus;
use App\Enums\ShipmentStatus;
use App\Enums\PricingType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('freights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained('users');
            $table->string('name');
            $table->string('description');
            $table->string('weight');
            $table->string('cityfrom');
            $table->string('cityto');
            $table->string('countryfrom');
            $table->string('countryto');
            $table->timestamp('datefrom');
            $table->timestamp('dateto');
            $table->boolean('is_published')->default(false)->nullable();
            $table->string('status')->default(FreightStatus::DRAFT->value);
            $table->string('shipment_status')->default(ShipmentStatus::INAPPLICABLE->value);
            $table->string('payment_option')->default(PricingType::FullBudget->value);
            $table->decimal('budget', 10, 2)->nullable();
            $table->string('carriage_rate')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('distance')->nullable();
            $table->boolean('is_read')->default(false);            
            $table->boolean('is_hazardous')->default(false)->nullable();            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('goods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });  
        
        Schema::create('freight_good', function (Blueprint $table) {
            $table->id();
            // Using foreignId and constrained for pivot table relationships is now the standard.
            $table->foreignId('freight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('good_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freight_good');
        Schema::dropIfExists('freights');
        Schema::dropIfExists('goods');
    }
};
