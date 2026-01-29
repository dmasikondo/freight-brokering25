<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add the column safely
        if (!Schema::hasColumn('lanes', 'uuid')) {
            Schema::table('lanes', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }

        // 2. Use chunk() instead of cursor() to avoid the 'fetch()' error
        // This processes 100 rows at a time, which is memory efficient
        DB::table('lanes')->whereNull('uuid')->orderBy('id')->chunk(100, function ($lanes) {
            foreach ($lanes as $lane) {
                DB::table('lanes')
                    ->where('id', $lane->id)
                    ->update(['uuid' => (string) \Illuminate\Support\Str::uuid()]);
            }
        });

        // 3. Apply the unique constraint now that data is populated
        Schema::table('lanes', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lanes', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
