<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worksheet_headers', function (Blueprint $table) {
            $table->string('worksheet_type', 20)
                ->default('scouting')
                ->after('name')
                ->comment('scouting | daily | weekly | monthly');
        });

    }

    public function down(): void
    {
        Schema::table('worksheet_headers', function (Blueprint $table) {
            $table->dropColumn('worksheet_type');
        });
    }
};