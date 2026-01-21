<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Naviisml\ApiGuard\Models\ApiKey;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('public_key')->unique();
            $table->text('private_key');
            $table->dateTime('revoked_at')->nullable();
            $table->dateTimes();
        });

        Schema::create('rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ApiKey::class)->constrained()->cascadeOnDelete();
            $table->integer('requests')->default(0);
            $table->integer('limit')->default(100);
            $table->dateTime('reset_at')->nullable();
            $table->dateTimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
