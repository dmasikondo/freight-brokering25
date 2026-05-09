<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add worksheet_type to worksheet_headers
        Schema::table('worksheet_headers', function (Blueprint $table) {
            $table->string('worksheet_type', 20)
                ->default('scouting')
                ->after('name')
                ->comment('scouting | daily | weekly | monthly');
        });

        // 2. Enable timestamps on worksheet_entries so we can track the 8-hour edit window
        //    The model currently has $timestamps = false — we enable updated_at only.
        //    We add created_at as well for completeness; both are nullable so existing rows are safe.
        Schema::table('worksheet_entries', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->after('sort_order');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('worksheet_headers', function (Blueprint $table) {
            $table->dropColumn('worksheet_type');
        });

        Schema::table('worksheet_entries', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};