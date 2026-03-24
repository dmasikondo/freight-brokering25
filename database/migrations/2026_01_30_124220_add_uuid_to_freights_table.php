<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Freight;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('freights', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('capacity_unit')->default('tonnes')->after('weight');
            $table->string('rate_type')->default('flat_rate')->after('carriage_rate');
        });

        // Populate existing data with UUIDs
        Freight::whereNull('uuid')->chunkById(100, function ($freights) {
            foreach ($freights as $freight) {
                $freight->update(['uuid' => (string) Str::uuid()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('freights', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'capacity_unit', 'rate_type']);
        });
    }
};