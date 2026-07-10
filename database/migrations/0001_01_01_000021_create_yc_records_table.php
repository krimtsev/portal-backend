<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yc_records', function (Blueprint $table) {
            // ID записи
            $table->unsignedBigInteger('record_id');

            // ID компании в YClients
            $table->unsignedBigInteger('company_id');

            // ID сотрудника
            $table->unsignedBigInteger('staff_id');

            // ID визита (может быть 0)
            $table->unsignedBigInteger('visit_id');

            /** Клиент */
            // ID клиента в YClients
            $table->unsignedBigInteger('client_id')->nullable();

            $table->string('client_name')->nullable();

            $table->string('client_phone', 30)->nullable();

            // Успешные визиты. Если 1, значит это был первый визит
            $table->integer('client_success_visits')->default(0);

            // Неуспешные визиты
            $table->integer('client_fail_visits')->default(0);

            // Статус визита
            $table->integer('visit_attendance');

            // Статус записи
            $table->integer('attendance');

            // Статус подтверждения записи
            $table->integer('confirmed');

            // Длительность сеанса
            $table->integer('length');

            // Фактическое время начала сеанса с таймзоной (ISO 8601)
            $table->dateTimeTz('datetime');

            $table->boolean('deleted')->default(false);

            // Финансовые итоги по записи (агрегируем из услуг для быстрой аналитики)
            // Собираем из сервисов
            $table->decimal('total_cost', 14)->default(0.00);

            $table->decimal('total_manual_cost', 14, 2)->default(0.00);

            $table->decimal('total_tariff_cost', 14, 2)->default(0.00);

            $table->decimal('total_base_tariff_cost', 14, 2)->default(0.00);

            $table->timestamps();

            // Индексы
            $table->primary('record_id');
            $table->index('company_id');
            $table->index('staff_id');
            $table->index('visit_id');
            $table->index('client_id');
            $table->index('datetime');
        });

        Schema::create('yc_record_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('record_id')
                ->constrained('yc_records', 'record_id')
                ->cascadeOnDelete();

            // ID самой услуги
            $table->unsignedBigInteger('service_id');

            $table->unsignedBigInteger('company_id');

            // Название услуги на момент записи (например: "МУЖСКАЯ СТРИЖКА")
            $table->string('title');

            // Стоимости
            $table->decimal('cost', 14, 2);

            $table->decimal('manual_cost', 14, 2);

            // Стоимость по допам + manual
            $table->decimal('tariff_cost', 14, 2);

            // Считаем только допы
            $table->decimal('base_tariff_cost', 14, 2);

            $table->decimal('discount', 14, 2)->default(0.00);

            $table->unsignedInteger('amount')->default(1);

            $table->timestamps();

            $table->unique(['record_id', 'service_id']);
            $table->index('company_id');
            $table->index('service_id');
        });

        Schema::create('yc_record_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('record_id')
                ->constrained('yc_records', 'record_id')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('document_id');

            $table->unsignedBigInteger('company_id');

            $table->integer('type_id');

            $table->string('type_title');

            $table->unsignedBigInteger('storage_id');

            $table->unsignedBigInteger('user_id');

            $table->dateTime('date_created');

            $table->unsignedBigInteger('visit_id');

            $table->timestamps();

            $table->unique(['record_id', 'document_id']);
            $table->index('company_id');
            $table->index('document_id');
        });

        Schema::create('yc_record_goods_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('record_id')
                ->constrained('yc_records', 'record_id')
                ->cascadeOnDelete();

            // Идентификатор складской операции (id goods_transactions)
            $table->unsignedBigInteger('transaction_id');

            $table->unsignedBigInteger('company_id');

            // Название товара
            $table->string('title');

            // $table->string('barcode')->nullable();

            // $table->string('article')->nullable();

            // Кол-во проданного товара
            $table->decimal('amount', 10, 4)->default(0.0000);

            // Цена за единицу товара
            $table->decimal('cost_per_unit', 14);

            // Стоимость с учетом скидок
            $table->decimal('cost', 14);

            // Стоимость с учетом предварительной скидки (за вычетом скидки переданной в параметре discount)
            $table->decimal('manual_cost', 14);

            $table->decimal('discount', 14)->default(0.00);

            // Привязки к сущностям
            $table->unsignedBigInteger('master_id')->nullable();

            // Склад списания товара
            $table->unsignedBigInteger('storage_id')->nullable();

            // Идентификатор товара
            $table->unsignedBigInteger('good_id')->nullable();

            // Идентификатор абонемента, который создан в результате продажи (Если товар является абонеметном)
            $table->unsignedBigInteger('loyalty_abonement_id');

            // Идентификатор сертификата, который создан в результате продажи (Если товар является сертификатом)
            $table->unsignedBigInteger('loyalty_certificate_id');

            // $table->string('good_special_number')->nullable();

            // Время визита
            $table->dateTime('datetime');

            // Кто оказывал УСЛУГУ в визите
            $table->unsignedBigInteger('record_staff_id')->nullable();

            $table->integer('attendance')->default(0);

            $table->timestamps();

            // Индексы для оптимизации
            $table->unique(['record_id', 'transaction_id']);
            $table->index('company_id');
            $table->index('datetime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yc_record_goods_transactions');
        Schema::dropIfExists('yc_record_documents');
        Schema::dropIfExists('yc_record_services');
        Schema::dropIfExists('yc_records');
    }
};
