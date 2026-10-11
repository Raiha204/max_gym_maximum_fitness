<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            if (!Schema::hasColumn('memberships', 'daily_credit_paused_until')) {
                $table->date('daily_credit_paused_until')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            if (Schema::hasColumn('memberships', 'daily_credit_paused_until')) {
                $table->dropColumn('daily_credit_paused_until');
            }
        });
    }
};
