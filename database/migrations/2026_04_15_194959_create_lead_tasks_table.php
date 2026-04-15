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
        // Инструмент для администраторов, чтобы не забыть перезвонить пациенту.

        Schema::create('lead_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();

            $table->dateTime('due_date');      // Крайний срок выполнения
            $table->dateTime('reminder_at')->nullable(); // Время для уведомления
            $table->dateTime('reminded_at')->nullable(); // Когда уведомление было отправлено

            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->string('priority')->default('medium'); // low, medium, high

            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['assigned_to', 'status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_tasks');
    }
};
