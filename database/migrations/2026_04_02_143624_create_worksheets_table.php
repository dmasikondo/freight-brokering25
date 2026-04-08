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
        Schema::create('worksheet_headers', function (Blueprint $table) {
            $table->id(); // int UNSIGNED, AUTO_INCREMENT

            // The Owner of the Worksheet
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Descriptive name (e.g., "beira lane scouting")
            $table->string('name', 255);

            // Completion Toggle (0 = Active, 1 = Archived/Completed)
            // Maps to your data where most are '1'
            $table->boolean('is_completed')->default(false);

            // Standard Laravel Timestamps
            $table->timestamps();
        });
        // Migration for Worksheets
        Schema::create('worksheet_entries', function (Blueprint $table) {
            $table->id(); // int UNSIGNED, AUTO_INCREMENT

            // Foreign Key to the Header (Transpartner 2025 Logistics)
            $table->foreignId('header_id')
                ->constrained('worksheet_headers')
                ->cascadeOnDelete();

            // Partner Information
            $table->string('partner_type', 50)->nullable()->default('general');
            $table->string('partner_name', 255);
            $table->string('contact_details', 255);

            // Task Content
            $table->text('activity')->nullable();
            $table->text('feedback')->nullable();
            $table->text('way_forward')->nullable();
            $table->text('private_notes')->nullable();

            // Scheduling & Reminders
            $table->dateTime('reminder_at')->nullable();
            $table->boolean('notified_as_reminder')->default(false); // tinyint(1)

            // Timestamps for Progress Tracking
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Organization & Audit
            $table->integer('sort_order');
            $table->foreignId('last_edited_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Laravel Standard Timestamps (Created_at / Updated_at)
            $table->timestamps();
        });

        // Migration for Sharing
        Schema::create('worksheet_header_user', function (Blueprint $table) {
            $table->id(); // int UNSIGNED, AUTO_INCREMENT

            // Reference to the Worksheet
            $table->foreignId('worksheet_header_id')
                ->constrained()
                ->cascadeOnDelete();

            // Reference to the Collaborator (Staff Member)
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Standard timestamps for tracking when sharing occurred
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worksheet_header_user');
        Schema::dropIfExists('worksheet_entries');
        Schema::dropIfExists('worksheet_headers');
    }
};
