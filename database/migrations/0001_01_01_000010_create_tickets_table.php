<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Отделы
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();

            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('departments')->insert([
            ['id' => 1, 'slug' => 'franchise',         'title' => 'Franchise'],
            ['id' => 2, 'slug' => 'build',             'title' => 'Build'],
            ['id' => 3, 'slug' => 'marketing',         'title' => 'Marketing'],
            ['id' => 4, 'slug' => 'network_admin',     'title' => 'Network Admin'],
            ['id' => 5, 'slug' => 'network_barbering', 'title' => 'Network Barbering'],
            ['id' => 6, 'slug' => 'community',         'title' => 'Community'],
            ['id' => 7, 'slug' => 'office_manager',    'title' => 'Office Manager'],
            ['id' => 8, 'slug' => 'it_department',     'title' => 'IT Department'],
            ['id' => 9, 'slug' => 'accounting',        'title' => 'Accounting'],
        ]);

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Название темы
            $table->string('title');

            // Для ответов в форме заявки
            // json ключей и значений { 'name': '...' }
            $table->json('attributes')->nullable();

            $table->string('type');

            $table->foreignId('department_id')->constrained('departments');
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
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets_files');
        Schema::dropIfExists('tickets_messages');
        Schema::dropIfExists('tickets_events');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('departments');
    }
}
