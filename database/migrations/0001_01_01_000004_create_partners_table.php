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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('organization')->nullable();
            $table->string('inn', 12)->nullable();
            $table->string('ogrnip', 15)->nullable();
            $table->string('name');
            $table->string('contract_number', 50)->nullable();
            $table->string('email')->nullable();
            $table->json('telnums')->nullable();
            $table->string('yclients_id')->nullable();
            $table->string('mango_telnum')->nullable();
            $table->string('address')->nullable();
            $table->date('start_at')->nullable();
            $table->boolean('disabled')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
