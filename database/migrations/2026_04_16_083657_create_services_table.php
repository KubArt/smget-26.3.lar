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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');        // Название: Tilda, Calltouch
            $table->string('slug')->unique(); // Код: tilda, calltouch
            $table->string('icon')->nullable(); // Иконка для UI
            $table->text('description')->nullable();
            $table->text('instruction')->nullable(); // Инструкция по настройке
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Сразу наполним базовыми данными
        \Illuminate\Support\Facades\DB::table('services')->insert([
            [
                'name' => 'Tilda',
                'slug' => 'tilda',
                'description' => 'Прием лидов через Webhook из форм Тильды',
                'instruction' => 'Скопируйте URL и вставьте его в настройки форм Tilda',
            ],
            [
                'name' => 'Calltouch',
                'slug' => 'calltouch',
                'description' => 'Интеграция звонков и заявок',
                'instruction' => 'Настройте отправку HTTP-запроса в ЛК Calltouch',
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
