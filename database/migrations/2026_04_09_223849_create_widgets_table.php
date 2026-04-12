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
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('type'); // wheel, popup, chat
            $table->string('name'); // Имя виджета
            $table->string('custom_name')->nullable(); // Имя виджета
            $table->string('privacy_config')->nullable(); // Политика конфиденциальности
            $table->text('settings')->nullable(); // Все настройки визуалов и шансов
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
