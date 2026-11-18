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
        Schema::table('cloud_files', function (Blueprint $table) {
            $table->unsignedBigInteger('size')
                ->default(0)
                ->after('ext');

            $table->string('checksum', 64)
                ->nullable()
                ->after('size');

            $table->foreignId('user_id')
                ->nullable()
                ->after('checksum')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('checksum');
        });

        Schema::table('cloud_folders', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('category_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cloud_files', function (Blueprint $table) {
            $table->dropIndex(['checksum']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['size', 'checksum', 'user_id']);
        });

        Schema::table('cloud_folders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
