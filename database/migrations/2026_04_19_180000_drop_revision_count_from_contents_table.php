<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            if (Schema::hasColumn('contents', 'revision_count')) {
                $table->dropColumn('revision_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->unsignedSmallInteger('revision_count')->default(0)->after('result');
        });
    }
};
