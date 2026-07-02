<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yc_company_daily_stats', function (Blueprint $table) {
            $table->decimal('income_average', 14, 2)->nullable()->after('income_services');
            $table->decimal('income_average_services', 14, 2)->nullable()->after('income_average');
        });
    }

    public function down(): void
    {
        Schema::table('yc_company_daily_stats', function (Blueprint $table) {
            $table->dropColumn(['income_average', 'income_average_services']);
        });
    }
};
