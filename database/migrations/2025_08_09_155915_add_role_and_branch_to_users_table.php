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
        Schema::table('users', function (Blueprint $table) {
            // Keep this lightweight to avoid FK ordering issues; we can add FK later if needed
            $table->string('role', 20)->default('branch_manager')->after('remember_token');
            $table->unsignedBigInteger('branch_id')->nullable()->after('role');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
            $table->dropColumn(['role', 'branch_id']);
        });
    }
};
