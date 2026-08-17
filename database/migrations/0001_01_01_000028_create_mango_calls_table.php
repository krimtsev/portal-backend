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
        Schema::create('mango_calls', function (Blueprint $table) {
            $table->id();

            // Идентификатор вызова
            $table->string('entry_id')->unique();

            // Статус звонка: 1 – входящий, 2 – исходящий, 3 – внутренний.
            $table->unsignedTinyInteger('context_type');

            // Признак успешности звонка: 0 – неуспешный, 1 – успешный.
            $table->unsignedTinyInteger('context_status');

            // Номер, с которого звонят
            $table->string('caller_number', 255)->nullable();

            // Номер, на который звонят
            $table->string('called_number', 255)->nullable();

            // Дата/время начала звонка, время в формате UTC.
            $table->datetime('context_start_time');

            // Продолжительность звонка в секундах.
            $table->integer('duration');

            $table->timestamps();

            $table->index('caller_number');
            $table->index('called_number');
            $table->index('context_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mango_calls');
    }
};
