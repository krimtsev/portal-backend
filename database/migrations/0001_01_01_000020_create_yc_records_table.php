<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yc_records', function (Blueprint $table) {
            $table->id();

            // ID записи
            $table->unsignedBigInteger('record_id');

            // ID компании в YClients
            $table->unsignedBigInteger('company_id');

            // ID сотрудника
            $table->unsignedBigInteger('staff_id');

            // ID визита (может быть 0)
            $table->unsignedBigInteger('visit_id');

            /** Клиент */
            $table->unsignedBigInteger('client_id')->nullable(); // ID клиента в YClients
            $table->string('client_name')->nullable();
            $table->string('client_phone', 30)->nullable();
            // Успешные визиты. Если 1, значит это был первый визит
            $table->integer('client_success_visits')->default(0);
            // Неуспешные визиты
            $table->integer('client_fail_visits')->default(0);

            // Фактическое время начала сеанса с таймзоной (ISO 8601)
            $table->dateTimeTz('datetime');

            // Фактическое время начала сеанса
            // $table->dateTime('date');
            // Дата подачи заявки
            // $table->dateTimeTz('create_date');
            // Дата изменения чего-то
            // $table->dateTimeTz('last_change_date');

            // Продолжительность сеанса
            // $table->unsignedInteger('seance_length');

            // Онлайн-запись или через админку
            // $table->boolean('online')->default(false);

            // Финансовые итоги по записи (агрегируем из услуг для быстрой аналитики)
            // Собираем из сервисов
            $table->decimal('total_cost', 14, 2)->default(0.00);
            $table->decimal('total_manual_cost', 14, 2)->default(0.00);

            $table->timestamps();

            // Индексы
            $table->unique(['company_id', 'record_id'], 'yc_records_company_record_unique');
            $table->index('staff_id');
            $table->index('visit_id');
            $table->index('client_id');
            $table->index('datetime');
        });

        Schema::create('yc_record_services', function (Blueprint $table) {
            $table->id();

            // Связь с нашей локальной таблицей записей
            $table->foreignId('record_id')->constrained('yc_records')->cascadeOnDelete();

            // ID самой услуги
            $table->unsignedBigInteger('service_id');

            // Название услуги на момент записи (например: "МУЖСКАЯ СТРИЖКА")
            $table->string('title');

            // Стоимости
            $table->decimal('cost', 14, 2);
            $table->decimal('manual_cost', 14, 2);
            $table->decimal('discount', 14, 2)->default(0.00);
            $table->unsignedInteger('amount')->default(1);

            $table->timestamps();

            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yc_record_services');
        Schema::dropIfExists('yc_records');
    }
};
