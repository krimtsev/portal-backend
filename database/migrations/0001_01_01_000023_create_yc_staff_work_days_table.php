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
        Schema::create('yc_staff_work_days', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('staff_id');

            $table->unsignedBigInteger('company_id');

            $table->date('date');

            $table->boolean('has_schedule')->default(false);
            $table->boolean('has_records')->default(false);
            $table->boolean('has_storage')->default(false);

            $table->timestamps();

            $table->unique(['staff_id', 'company_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yc_staff_work_days');
    }
};
