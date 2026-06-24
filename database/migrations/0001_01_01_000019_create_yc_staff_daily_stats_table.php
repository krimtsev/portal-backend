<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yc_staff_daily_stats', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('staff_id');

            // ID компании в YClients
            $table->unsignedInteger('company_id');

            // Дата среза
            $table->date('date');

            // Финансовые показатели
            $table->decimal('income_total', 14, 2);

            $table->decimal('income_goods', 14, 2);

            $table->decimal('income_services', 14, 2);

            $table->decimal('income_average', 14, 2);

            $table->decimal('income_average_services', 14, 2);

            // Операционные показатели
            $table->decimal('fullness_percent', 6, 2);

            // Записи
            $table->unsignedInteger('record_completed');

            $table->unsignedInteger('record_pending');

            $table->unsignedInteger('record_canceled');

            $table->unsignedInteger('record_total');

            // Клиенты
            $table->unsignedInteger('client_new');

            $table->unsignedInteger('client_return');

            $table->unsignedInteger('client_active');

            $table->unsignedInteger('client_lost');

            $table->timestamps();

            // Защита от дублей + быстрый поиск
            $table->unique(['company_id', 'staff_id', 'date'], 'yc_staff_daily_unique');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yc_staff_daily_stats');
    }
};
