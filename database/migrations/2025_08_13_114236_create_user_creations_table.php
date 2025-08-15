<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_creations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('creator_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Ensure a user can only be created once by a single person
            $table->unique(['created_user_id', 'creator_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_creations');
    }
};