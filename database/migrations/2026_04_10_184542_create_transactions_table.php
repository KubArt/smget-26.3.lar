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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('amount'); // Положительное (дебет) или отрицательное (кредит)
            $table->string('type'); // 'deposit' (пополнение), 'withdraw' (списание)
            $table->string('description')->nullable(); // "Активация ваучера XXX" или "Продление тарифа LIGHT"
            $table->nullableMorphs('source'); // Ссылка на модель (Voucher, PaymentGate и т.д.)
            $table->timestamps();
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
