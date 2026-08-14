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
        Schema::create('mango_blacklist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('number_id')->unique();
            $table->string('number');
            $table->string('number_type')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->index('number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mango_blacklist');
    }
};
