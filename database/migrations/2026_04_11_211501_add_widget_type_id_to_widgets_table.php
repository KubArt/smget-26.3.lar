<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('widgets', function (Blueprint $table) {
            // 1. Добавляем внешний ключ после site_id
            // nullable() нужен, если в таблице уже есть данные, чтобы не поймать ошибку
            $table->foreignId('widget_type_id')
                ->after('site_id')
                ->nullable()
                ->constrained('widget_types')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropForeign(['widget_type_id']);
            $table->dropColumn('widget_type_id');
        });
    }
};
