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
        Schema::create('site_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            // Тип драйвера (yandex, vk, ga4 и т.д.)
            $table->string('type');
            // Все настройки (counter_id, token, специальные цели) храним в JSON
            $table->text('settings')->nullable();
            // Общий статус интеграции
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            // Индекс для быстрого поиска интеграций конкретного сайта
            $table->index(['site_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_metrics');
    }
};
