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
        Schema::create('yc_comments', function (Blueprint $table) {
            $table->id();

            // ID комментария, для уникальности записи
            $table->unsignedBigInteger('comment_id');

            $table->unsignedBigInteger('company_id');

            // salon_id (не всегда company_id)
            $table->unsignedBigInteger('salon_id');

            // master_id
            // ID мастера, если type = 1
            $table->unsignedBigInteger('staff_id')->nullable();

            // Оценка (от 1 до 5)
            $table->unsignedTinyInteger('rating');

            // 1 - комментарий к мастеру, 0 - к салону
            $table->integer('type')->default(1);

            // Дата, когда был оставлен комментарий
            $table->dateTime('date');

            $table->timestamps();

            // Индексы для быстрой фильтрации и аналитики
            $table->unique(['company_id', 'comment_id'], 'yc_comments_company_comment_unique');
            $table->index('staff_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yc_comments');
    }
};
