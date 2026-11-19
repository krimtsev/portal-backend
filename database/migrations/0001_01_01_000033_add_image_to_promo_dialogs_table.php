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
        Schema::table('promo_dialogs', function (Blueprint $table): void {
            $table->string('image')->nullable()->after('description');

            $table->foreignId('user_id')
                ->nullable()
                ->after('disabled')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promo_dialogs', function (Blueprint $table): void {
            $table->dropColumn('image');
            $table->dropColumn('user_id');
            $table->dropForeign(['user_id']);
        });
    }
};
