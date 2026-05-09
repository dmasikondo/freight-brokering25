<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worksheet_entries', function (Blueprint $table) {
            // Adding the main action column
            $table->string('planned_action')->nullable()->after('partner_type');
            
            // Adding the custom text column for when "custom" is selected
            $table->text('planned_action_custom')->nullable()->after('planned_action');
        });
    }

    public function down(): void
    {
        Schema::table('worksheet_entries', function (Blueprint $table) {
            $table->dropColumn(['planned_action', 'planned_action_custom']);
        });
    }
};