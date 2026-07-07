<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yc_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->primary();

            $table->unsignedBigInteger('company_id');

            $table->unsignedBigInteger('master_id')->nullable();

            $table->unsignedBigInteger('document_id')->default(0);

            $table->unsignedBigInteger('record_id')->default(0);

            $table->unsignedBigInteger('visit_id')->default(0);

            $table->unsignedBigInteger('account_id')->nullable();

            $table->string('account_title')->nullable();

            $table->unsignedBigInteger('client_id')->nullable();

            $table->decimal('amount', 14);

            // Даты приходят с таймзоной (2026-04-01T22:46:00+0400)
            $table->dateTime('date');

            // услуга или товар
            $table->unsignedBigInteger('sold_item_id')->nullable();

            // 'service', 'goods_transaction', null
            $table->string('sold_item_type')->nullable();

            // Статья расходов / категория операции (expense)
            $table->unsignedInteger('expense_id')->nullable();

            // "Зарплата персонала", "Оказание услуг"
            $table->string('expense_title')->nullable();

            $table->unsignedInteger('expense_type')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'date'], 'yc_trans_company_date_idx');
            $table->index(['company_id', 'master_id', 'date'], 'yc_trans_master_date_idx');
            $table->index('record_id');
            $table->index('document_id');
            $table->index('visit_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yc_transactions');
    }
};
