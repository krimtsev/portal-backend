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
        Schema::create('yc_company_staff', function (Blueprint $table) {
            // ID - сотрудника
            $table->unsignedBigInteger('staff_id')->primary();

            // company_id - ID филиала
            $table->unsignedBigInteger('company_id');

            // Имя сотрудника
            $table->string('name');

            $table->string('firstname')->nullable();

            $table->string('surname')->nullable();

            // Специализация
            $table->string('specialization');

            // Уволен или нет
            $table->boolean('fired')->default(false);

            // Когда был уволен
            $table->date('dismissal_date')->nullable();

            // Рейтинг сотрудника
            $table->decimal('rating', 3, 2)->default(0.00);

            $table->string('avatar')->nullable();

            $table->string('avatar_big')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'staff_id'], 'yclients_staff_company_client_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yc_company_staff');
    }
};
