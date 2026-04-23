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
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('widget_id')->nullable()->constrained('widgets')->onDelete('set null');

            // Данные приза
            $table->string('code')->unique();           // Уникальный промокод
            $table->string('name');                      // Название приза (Скидка 10%)
            $table->text('description')->nullable();     // Описание приза
            $table->string('type')->default('discount'); // discount, gift, service, cashback
            $table->text('meta')->nullable();            // Доп. данные (процент, сумма, ссылка)

            // Сроки действия
            $table->timestamp('expires_at')->nullable(); // Когда истекает
            $table->timestamp('activated_at')->nullable(); // Когда активировали
            $table->timestamp('used_at')->nullable();     // Когда использовали

            // Статусы
            $table->boolean('is_active')->default(true);   // Активен ли приз
            $table->boolean('is_used')->default(false);    // Использован ли
            $table->boolean('is_limited')->default(false); // Ограниченный по времени?

            // Кто использовал
            $table->string('used_by_contact')->nullable(); // Контакт использовавшего
            $table->string('used_by_ip')->nullable();      // IP использовавшего

            $table->timestamps();

            // Индексы
            $table->index(['site_id', 'code']);
            $table->index(['client_id', 'is_used']);
            $table->index(['lead_id', 'is_used']);
            $table->index(['expires_at', 'is_active']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};
