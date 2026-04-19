<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('topup_requests');
        Schema::dropIfExists('user_credits');
    }

    public function down(): void
    {
        // Intentionally empty: tables removed from schema; restore via VCS history if needed.
    }
};
