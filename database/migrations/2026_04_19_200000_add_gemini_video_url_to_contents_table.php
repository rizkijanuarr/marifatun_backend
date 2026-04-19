<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->text('gemini_video_url')->nullable()->after('video_cta');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('gemini_video_url');
        });
    }
};
