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
        Schema::create('promo_dialogs', function (Blueprint $table) {
            $table->id();

            // Код подготовленного шаблона на фронте.
            $table->string('code', 100)->nullable()->unique();

            // Короткий заголовок диалога
            $table->string('title', 150);

            // Описание (текст) диалога
            $table->text('description')->nullable();

            // Тип сообщения: template — подготовленный шаблон на фронте, custom — произвольный
            $table->string('type');

            // Признак включённого диалога
            $table->boolean('disabled')->default(true);

            $table->timestamps();

            $table->index(['disabled', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_dialogs');
    }
};
