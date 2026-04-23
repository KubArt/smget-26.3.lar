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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('widget_id')->nullable()->constrained('widgets')->onDelete('set null');

            $table->string('phone', 20)->nullable();
            $table->string('email', 50)->nullable();

            $table->text('form_data')->nullable(); // Все поля из виджета
            $table->string('status')->default('new'); // Ссылка на код стадии воронки
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            $table->text('vaucher_name')->nullable();
            $table->text('vaucher_code')->nullable();
            $table->timestamp('vaucher_end_date')->nullable();
            $table->integer('vaucher_is_active')->default(0);
            $table->integer('is_blocked')->default(0);

            $table->text('description')->nullable();
            $table->integer('tag')->default(0);

            // Маркетинг
            $table->string('utm_source', 255)->nullable();
            $table->string('utm_campaign', 255)->nullable();
            $table->string('utm_medium', 255)->nullable();
            $table->string('utm_term', 255)->nullable();
            $table->string('utm_content', 255)->nullable();
            $table->string('utm_referrer', 255)->nullable();
            $table->string('page_url', 500)->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Индексы для фильтрации в админке
            $table->index(['site_id', 'status']);
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
