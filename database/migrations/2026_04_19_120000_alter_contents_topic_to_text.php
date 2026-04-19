<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Upgrade: kolom `topic` dari VARCHAR(255) ke TEXT (topik panjang hingga ~1000 kata).
 * Fresh install sudah memakai TEXT di migration create; ini untuk basis data yang sudah jalan sebelumnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contents')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE contents MODIFY topic TEXT NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('contents')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE contents MODIFY topic VARCHAR(255) NOT NULL');
        }
    }
};
