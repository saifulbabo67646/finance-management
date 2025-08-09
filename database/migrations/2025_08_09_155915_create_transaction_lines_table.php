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
        Schema::create('transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('branch_id')->constrained('branches');
            $table->decimal('debit', 14, 2)->nullable();
            $table->decimal('credit', 14, 2)->nullable();
            $table->timestamps();

            $table->index(['transaction_id']);
            $table->index(['account_id']);
            $table->index(['branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_lines');
    }
};
