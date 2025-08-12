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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title');
            $table->timestamps();
             $table->softDeletes();
        });

        // Add the pre-defined roles
        DB::table('roles')->insert([
            ['name' => 'shipper', 'title'=>'sh'],
            ['name' => 'carrier', 'title'=>'ca'],
            ['name' => 'admin', 'title'=>'admin'],
            ['name' => 'superadmin', 'title'=>'superadmin'],
            ['name' => 'procurement logistics associate', 'title'=>'pla'],
            ['name' => 'marketing logistics associate', 'title'=>'mla'],
            ['name' => 'procurement executive associate', 'title'=>'pea'],
            ['name' => 'operations logistics associate', 'title'=>'ola'],
            ['name' => 'accountant','title'=>'accounts'],
        ]);

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->string('classification')->nullable(); // 'primary' or 'secondary'
            $table->primary(['user_id', 'role_id']);
            $table->timestamps();            
            $table->softDeletes();
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
