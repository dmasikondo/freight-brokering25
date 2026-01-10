<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Standard Laravel Verification field (usually goes after email)
            $table->timestamp('email_verified_at')->nullable()->after('email');

            // Suspension Columns
            $table->dateTime('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->foreignId('suspended_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Approval Columns
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['suspended_by_id']);
            $table->dropForeign(['approved_by_id']);
            
            $table->dropColumn([
                'email_verified_at',
                'suspended_at',
                'suspension_reason',
                'suspended_by_id',
                'approved_at',
                'approved_by_id',
            ]);
        });
    }
};