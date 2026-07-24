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
        Schema::create('event_calendars', function (Blueprint $table) {
            $table->id();

            // Заголовок
            $table->string('title');

            // Описание
            $table->text('description')->nullable();

            // ID пользователя, кто создал календарь событий
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // ID отдела (может быть пустым)
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            // Период события: начало и конец
            $table->date('start_at');
            $table->date('end_at');


            $table->timestamps();
        });

        Schema::create('event_calendar_user', function (Blueprint $table) {
            $table->id();

            // Связь с календарем событий
            $table->foreignId('event_calendar_id')
                ->constrained('event_calendars')
                ->cascadeOnDelete();

            // Связь с ответственным пользователем
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['event_calendar_id', 'user_id'], 'calendar_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_calendar_user');
        Schema::dropIfExists('event_calendars');
    }
};
