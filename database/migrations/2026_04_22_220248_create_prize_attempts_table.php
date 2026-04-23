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
        // Таблица для отслеживания попыток получения призов
        Schema::create('prize_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prize_id')->nullable()->constrained('prizes')->onDelete('set null');
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('widget_id')->nullable()->constrained('widgets')->onDelete('set null');
            $table->string('contact');                     // Контакт пользователя
            $table->string('prize_code');                  // Какой код пытались получить
            $table->boolean('is_success')->default(false); // Успешно ли
            $table->string('error_code')->nullable();      // Код ошибки
            $table->ipAddress()->nullable();               // IP пользователя
            $table->text('user_agent')->nullable();        // User agent
            $table->timestamps();

            $table->index(['site_id', 'contact']);
            $table->index(['site_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_attempts');
    }
};
