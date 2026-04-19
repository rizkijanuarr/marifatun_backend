<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('video_platform')->nullable()->after('tone');
            $table->text('video_key_message')->nullable()->after('video_platform');
            $table->text('video_cta')->nullable()->after('video_key_message');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['video_platform', 'video_key_message', 'video_cta']);
        });
    }
};
