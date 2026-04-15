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
        Schema::create('funnel_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');

            $table->string('name', 100); // Публичное название ("Первичная консультация")
            $table->string('code', 50);  // Технический код (new, appointment, lost)
            $table->integer('sort_order')->default(0);
            $table->string('color', 7)->default('#6c757d'); // Цвет для интерфейса

            $table->boolean('is_system')->default(false); // Системные нельзя удалять
            $table->integer('probability')->default(0); // Вероятность закрытия в %

            $table->timestamps();

            $table->index(['site_id', 'sort_order']);
            $table->unique(['site_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funnel_stages');
    }
};
