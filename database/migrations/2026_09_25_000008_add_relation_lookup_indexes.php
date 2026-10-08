<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('memberships') && Schema::hasColumn('memberships', 'member_id')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->index('member_id');
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'membership_id') && Schema::hasColumn('payments', 'paid_at')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index(['membership_id', 'paid_at']);
            });
        }

        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'membership_id') && Schema::hasColumn('attendances', 'checked_in_at')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->index(['membership_id', 'checked_in_at']);
            });
        }

        if (Schema::hasTable('maintenance') && Schema::hasColumn('maintenance', 'equipment_id') && Schema::hasColumn('maintenance', 'reported_at')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->index(['equipment_id', 'reported_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenance') && Schema::hasColumn('maintenance', 'equipment_id') && Schema::hasColumn('maintenance', 'reported_at')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->dropIndex(['equipment_id', 'reported_at']);
            });
        }

        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'membership_id') && Schema::hasColumn('attendances', 'checked_in_at')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropIndex(['membership_id', 'checked_in_at']);
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'membership_id') && Schema::hasColumn('payments', 'paid_at')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex(['membership_id', 'paid_at']);
            });
        }

        if (Schema::hasTable('memberships') && Schema::hasColumn('memberships', 'member_id')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->dropIndex(['member_id']);
            });
        }
    }
};
