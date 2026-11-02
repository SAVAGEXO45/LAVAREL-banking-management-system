<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->string('reference')->unique();

            $table->enum('type', ['deposit', 'withdrawal']);

            $table->decimal('amount', 15, 2);

            $table->decimal('balance_before', 15, 2);

            $table->decimal('balance_after', 15, 2);

            $table->string('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
