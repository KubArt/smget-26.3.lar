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
        // Для лога и аналитики: кто, когда и почему перевел лид на другой этап.

            Schema::create('lead_stage_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');

                $table->string('from_stage')->nullable(); // Код предыдущей стадии
                $table->string('to_stage');              // Код новой стадии

                $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('comment')->nullable(); // Причина смены статуса

                $table->timestamp('created_at')->useCurrent();

                $table->index('lead_id');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_stage_history');
    }
};
