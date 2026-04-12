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
        Schema::create('widget_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->constrained()->onDelete('cascade');
            $table->enum('event_type', ['view', 'click']);
            $table->string('url')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string("utm_medium")->nullable();
            $table->string("utm_content")->nullable();
            $table->string("utm_term")->nullable();
            $table->string('ip')->nullable();
            $table->string("user_agent")->nullable();
            $table->string("referer")->nullable();
            $table->string("query")->nullable();
            $table->string("session_id")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_statistics');
    }
};
