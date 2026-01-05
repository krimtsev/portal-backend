<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tickets_categories', function (Blueprint $table) {
            $table->id();

            // Название категории
            $table->string('title');
            $table->string('slug')->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Название темы
            $table->string('title');

            // Для ответов в форме заявки
            // json ключей и значений { 'name': '...' }
            $table->json('attributes')->nullable();

            $table->string('type');

            $table->foreignId('category_id')->constrained('tickets_categories');
            $table->foreignId('partner_id')->constrained('partners');
            $table->foreignId('user_id')->constrained('users');
            $table->string('state');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tickets_messages', function (Blueprint $table) {
            $table->id();

            // Для кастомного текста
            $table->text('text')->nullable();
            $table->foreignId('ticket_id')->constrained('tickets');
            $table->foreignId('user_id')->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tickets_files', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('name');
            $table->string('origin');
            $table->string('path');
            $table->string('type');
            $table->string('ext');
            $table->foreignId('ticket_message_id')->constrained('tickets_messages');

            $table->timestamps();
        });

        Schema::create('tickets_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets');
            $table->json('changes');
            $table->foreignId('user_id')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets_files');
        Schema::dropIfExists('tickets_messages');
        Schema::dropIfExists('tickets_events');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('tickets_categories');
    }
}
