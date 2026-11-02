<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_number')->unique();
            $table->enum('account_type', ['savings', 'current'])->default('savings');
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('NGN');
            $table->enum('status', ['active', 'inactive', 'frozen'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
