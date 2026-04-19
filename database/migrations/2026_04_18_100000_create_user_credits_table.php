<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_credits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->integer('credits')->default(0);
            $table->timestamp('last_daily_claim')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamp('createdDate')->nullable();
            $table->timestamp('modifiedDate')->nullable();
            $table->timestamp('deletedDate')->nullable();
            $table->string('createdBy')->nullable();
            $table->string('modifiedBy')->nullable();
            $table->string('deletedBy')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_credits');
    }
};
