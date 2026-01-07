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
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->string('auditable_type');
    $table->unsignedBigInteger('auditable_id');
    $table->unsignedBigInteger('actor_id')->nullable();
    $table->string('event'); // created, updated, deleted
    $table->json('payload'); // Old/New values
    $table->string('ip_address');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
