<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('api_keys') || Schema::hasColumn('api_keys', 'user_id')) {
            return;
        }

        Schema::table('api_keys', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('api_keys') && Schema::hasColumn('api_keys', 'user_id')) {
            Schema::table('api_keys', function (Blueprint $table): void {
                $table->dropColumn('user_id');
            });
        }
    }
};
