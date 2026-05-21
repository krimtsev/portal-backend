<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * php artisan tinker database/patches/remove_ticket_category.php
 */
DB::transaction(function () {
    if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'category_id')) {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['category_id']);

            $table->renameColumn('category_id', 'department_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade');
        });

        echo "✅ Таблица 'tickets': category_id -> department_id (updated)\n";
    }

    if (Schema::hasTable('tickets_events')) {
        DB::table('tickets_events')
            ->where('changes', 'like', '%category_id%')
            ->chunkById(100, function ($events) {
                foreach ($events as $event) {
                    $changes = json_decode($event->changes, true);

                    if (isset($changes['category_id'])) {
                        $changes['department_id'] = $changes['category_id'];
                        unset($changes['category_id']);

                        DB::table('tickets_events')
                            ->where('id', $event->id)
                            ->update(['changes' => json_encode($changes)]);
                    }
                }
            });

        echo "✅ Таблица 'tickets_events': ключи в JSON обновлены\n";
    }

    if (Schema::hasTable('tickets_categories')) {
        Schema::drop('tickets_categories');
        echo "✅ Таблица 'tickets_categories' удалена\n";
    }
});

echo 'Патч успешно выполнен!';
