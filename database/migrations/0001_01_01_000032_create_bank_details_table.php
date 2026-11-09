<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('partner_id')
                ->nullable()
                ->constrained('partners')
                ->nullOnDelete();

            $table->string('entrepreneur_name')
                ->comment('ФИО предпринимателя');

            $table->char('inn', 12)
                ->comment('ИНН');

            $table->char('checking_account', 20)
                ->comment('Расчётный счёт');

            $table->string('bank_name')
                ->comment('Наименование банка');

            $table->char('bik', 9)
                ->comment('БИК');

            $table->char('correspondent_account', 20)
                ->comment('Корреспондентский счёт');

            $table->timestamps();

            $table->index(['user_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_details');
    }
};
