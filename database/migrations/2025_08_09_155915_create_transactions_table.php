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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->date('date');
            $table->string('type', 20); // cash, bank, contra, journal
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('from_account_id')->constrained('accounts');
            $table->foreignId('to_account_id')->constrained('accounts');
            $table->decimal('amount', 14, 2);
            $table->text('narration')->nullable();
            // Optional bank info
            $table->string('bank_name')->nullable();
            $table->string('cheque_no', 50)->nullable();
            $table->date('cheque_date')->nullable();
            // Attribution
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['date']);
            $table->index(['branch_id']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
