<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yc_tariffs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('service_id');

            $table->string('title')->nullable();

            $table->decimal('cost', 14)->nullable();

            $table->date('start_date');

            $table->date('end_date')->nullable();

            $table->boolean('disabled')->default(0);

            $table->timestamps();

            $table->index(['service_id', 'start_date', 'end_date'], 'tariff_search_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yc_tariffs');
    }
};
