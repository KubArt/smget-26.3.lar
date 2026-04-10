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
        Schema::create('notification_read_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Используем UUID, так как в Laravel Notifications ID по умолчанию — UUID
            $table->uuid('notification_id');

            $table->timestamps();

            // Индекс для защиты от дублей и быстрого поиска
            $table->unique(['user_id', 'notification_id']);

            // Индекс для связи с таблицей уведомлений (не ставим foreignId,
            // так как Laravel может хранить уведомления в разных местах)
            $table->index('notification_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_read_states');
    }
};
