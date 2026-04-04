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
        // Migration for Worksheets
        Schema::create('worksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // The owner/creator
            $table->foreignId('partner_id')->nullable()->constrained('users'); // Link to user table
            $table->string('partner_name'); // Custom name or cached name
            $table->string('contact_details');
            $table->text('activity')->nullable();
            $table->text('feedback')->nullable();
            $table->text('way_forward')->nullable();
            $table->date('entry_date');
            $table->timestamps();
        });

        // Migration for Sharing
        Schema::create('worksheet_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worksheet_shares');
        Schema::dropIfExists('worksheets');
    }
};
