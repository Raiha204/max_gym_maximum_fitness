<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            if (!Schema::hasColumn('memberships', 'remaining_amount')) {
                $table->decimal('remaining_amount', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('memberships', 'daily_credit_enabled')) {
                $table->boolean('daily_credit_enabled')->default(false);
            }
            if (!Schema::hasColumn('memberships', 'daily_credit_started_at')) {
                $table->timestamp('daily_credit_started_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $columns = ['remaining_amount', 'daily_credit_enabled', 'daily_credit_started_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('memberships', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
