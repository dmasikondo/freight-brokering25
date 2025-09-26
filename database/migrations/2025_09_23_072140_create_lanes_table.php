<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LaneStatus;
use App\Enums\VehiclePositionStatus;
use App\Enums\TrailerType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lanes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('location')->nullable();
            $table->string('destination')->nullable();
            $table->string('countryfrom')->nullable();
            $table->string('cityfrom')->nullable();
            $table->string('countryto')->nullable();
            $table->string('cityto')->nullable();
			$table->string('trailer')->default(TrailerType::FLAT_BED->value);
			$table->string('capacity');
            $table->string('rate');
			$table->timestamp('availability_date');            
            $table->string('status')->default(LaneStatus::DRAFT->value);
            $table->string('vehicle_status')->default(VehiclePositionStatus::INAPPLICABLE->value);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lanes');
    }
};
