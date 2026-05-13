<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * php artisan tinker database/patches/create_departments.php
 */
if (!Schema::hasTable('departments')) {
    Schema::create('departments', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();

        $table->timestamps();
        $table->softDeletes();
    });

    DB::table('departments')->insert([
        ['id' => 1, 'slug' => 'franchise', 'title' => 'Franchise'],
        ['id' => 2, 'slug' => 'build', 'title' => 'Build'],
        ['id' => 3, 'slug' => 'marketing', 'title' => 'Marketing'],
        ['id' => 4, 'slug' => 'network_admin', 'title' => 'Network Admin'],
        ['id' => 5, 'slug' => 'network_barbering', 'title' => 'Network Barbering'],
        ['id' => 6, 'slug' => 'community', 'title' => 'Community'],
        ['id' => 7, 'slug' => 'office_manager', 'title' => 'Office Manager'],
        ['id' => 8, 'slug' => 'it_department', 'title' => 'IT Department'],
        ['id' => 9, 'slug' => 'accounting', 'title' => 'Accounting'],
    ]);

    echo "Таблица departments создана.";
} else {
    echo "Таблица departments уже существует.";
}

