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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('code')->unique();
            $table->integer('amount')->default(0); // Если это денежный ваучер
            $table->foreignId('plan_id')->nullable()->constrained(); // Если ваучер на конкретный тариф
            $table->timestamp('expires_at')->nullable(); // Срок годности самого кода
            $table->integer('uses')->default(1); // Сколько раз можно активировать (обычно 1)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
