<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yc_storage_transactions', callback: function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->primary();

            $table->unsignedBigInteger('company_id');

            $table->unsignedBigInteger('master_id')->nullable();
            // $table->string('master_title')->nullable();

            $table->unsignedBigInteger('document_id');

            $table->unsignedInteger('type_id');

            $table->string('type');

            $table->unsignedTinyInteger('operation_unit_type');

            $table->decimal('amount', 12);

            // "2026-05-19T18:54:00+0400"
            $table->dateTimeTz('create_date');

            $table->decimal('cost_per_unit', 12);

            $table->decimal('cost', 12);

            $table->decimal('discount', 12);

            $table->text('comment')->nullable();

            $table->unsignedBigInteger('record_id')->default(0);

            $table->unsignedBigInteger('loyalty_abonement_id')->default(0);

            $table->unsignedBigInteger('loyalty_certificate_id')->default(0);

            // Товар
            $table->unsignedBigInteger('good_id');
            $table->string('good_title');

            // Единица измерения (Unit)
            // $table->unsignedBigInteger('unit_id');
            // $table->string('unit_title');

            // Склад
            $table->unsignedBigInteger('storage_id')->nullable();
            $table->string('storage_title')->nullable();

            // Клиент
            $table->unsignedBigInteger('client_id')->nullable();
            // $table->string('client_name')->nullable();
            // $table->string('client_phone')->nullable();

            // Услуга
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('service_title')->nullable();

            // Поставщик
            // $table->unsignedBigInteger('supplier_id')->nullable()->index();
            // $table->string('supplier_title')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('master_id');
            $table->index('good_id');
            $table->index(['company_id', 'document_id', 'master_id'], 'yc_storage_document_master_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yc_storage_transactions');
    }
};
