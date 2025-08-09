<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->date('date');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_sequences');
    }
};
