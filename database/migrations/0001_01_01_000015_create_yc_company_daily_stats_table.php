<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yc_company_daily_stats', function (Blueprint $table) {
            $table->id();

            // ID компании в YClients
            $table->unsignedInteger('company_id');

            // Дата среза
            $table->date('date');

            // Финансовые показатели
            $table->decimal('income_total', 14, 2);

            $table->decimal('income_goods', 14, 2);

            $table->decimal('income_services', 14, 2);

            // Операционные показатели
            $table->decimal('fullness_percent', 6, 2); // Загруженность за день

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

            $table->unsignedInteger('client_total');

            $table->timestamps();

            // Защита от дублей + быстрый поиск
            $table->unique(['company_id', 'date'], 'yc_company_daily_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yc_company_daily_stats');
    }
};
