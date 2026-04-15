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
        // Автоматизация: например, лиды с определенной UTM-меткой сразу назначаются на конкретного менеджера.

        Schema::create('lead_routing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('name');

            // Условия в формате JSON: [{"field": "utm_source", "operator": "=", "value": "yandex"}]
            $table->text('conditions')->nullable();

            // Действие: на кого назначить
            $table->foreignId('assign_to_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Действие: на какую стадию перевести
            $table->string('set_stage_code')->nullable();

            $table->integer('priority')->default(0); // Порядок применения правил
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['site_id', 'is_active', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_routing_rules');
    }
};
