<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('content_type');
            $table->text('topic');
            $table->text('keywords')->nullable();
            $table->string('target_audience')->nullable();
            $table->string('tone');
            $table->longText('result')->nullable();
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
        Schema::dropIfExists('contents');
    }
};
