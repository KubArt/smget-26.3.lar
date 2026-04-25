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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название проекта
            $table->string('domain')->unique(); // smile-center.ru
            $table->string('email'); // smile-center.ru
            $table->uuid('api_key')->unique(); // Ключ для JS-виджета
            $table->boolean('is_active')->default(true);
            //общее количество показов виджетов за отчетный период
            $table->integer('total_widgets_show')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
