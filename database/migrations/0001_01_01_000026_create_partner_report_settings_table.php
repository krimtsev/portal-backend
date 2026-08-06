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
        Schema::create('partner_report_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('partner_id')->unique()
                ->constrained()
                ->onDelete('cascade');

            // Потерянные клиенты
            $table->unsignedSmallInteger('lost_clients_days')->default(0);

            // Потерянные клиенты
            $table->unsignedSmallInteger('returned_clients_days')->default(0);

            // Новые клиенты
            $table->unsignedSmallInteger('new_clients_days')->default(0);

            $table->timestamps();
        });

        Schema::create('partner_notification_channels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('partner_id')->unique()
                ->constrained()
                ->onDelete('cascade');

            // Нужно ли отправлять сообщение в телеграм
            $table->boolean('send_telegram')->default(false);

            // Telegram ID
            $table->bigInteger('telegram_chat_id')->nullable();

            // Нужно ли учитывать дату оплаты сервиса
            $table->boolean('check_payment')->default(false);

            // Дата оплаты сервиса
            $table->date('payment_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_notification_channels');
        Schema::dropIfExists('partner_report_settings');
    }
};
