<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tender_offers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Polymorphic relation (Freight or Lane)
            $table->morphs('tenderable');

            // The carrier or shipper making the offer
            $table->foreignId('carrier_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Offer financials
            $table->decimal('amount', 10, 2);

            // Proposed dates
            $table->date('proposed_pickup_date')->nullable();
            $table->date('proposed_delivery_date')->nullable();

            // Supporting info
            $table->text('notes')->nullable();

            // Status
            $table->string('status')->default('pending');

            // Ranking (recalculated on each new offer)
            $table->unsignedInteger('ranked_position')->nullable();

            // Award tracking
            $table->timestamp('awarded_at')->nullable();
            $table->foreignId('awarded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Rejection
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Prevent a carrier from having two active offers on same tender
            $table->unique(
                ['tenderable_type', 'tenderable_id', 'carrier_id'],
                'unique_active_offer'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_offers');
    }
};