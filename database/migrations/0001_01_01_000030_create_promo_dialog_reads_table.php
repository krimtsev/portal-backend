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
        Schema::create('promo_dialog_reads', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('promo_dialog_id');

            $table->unsignedBigInteger('user_id');

            // Дата, когда пользователь нажал «Ознакомлен»
            $table->timestamp('read_at')->useCurrent();

            $table->timestamps();

            // С одним диалогом пользователь может ознакомиться только один раз
            $table->unique(['promo_dialog_id', 'user_id']);

            $table->index('user_id');

            $table->foreign('promo_dialog_id')
                ->references('id')
                ->on('promo_dialogs')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_dialog_reads');
    }
};
