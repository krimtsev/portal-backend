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
            $table->id();

            // ID - сотрудника
            $table->unsignedBigInteger('staff_id');

            // company_id - ID филиала
            $table->unsignedBigInteger('company_id');

            // Имя сотрудника
            $table->string('name');

            $table->string('firstname');

            $table->string('surname');

            // Специализация
            $table->string('specialization');

            // Уволен или нет
            $table->boolean('is_fired')->default(false);

            // Когда был уволен
            $table->date('dismissal_date')->nullable();

            // Рейтинг сотрудника
            $table->decimal('rating', 3, 2)->default(0.00);

            $table->timestamps();

            $table->unique(['company_id', 'staff_id'], 'yclients_staff_company_client_unique');
            $table->index('staff_id');
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
