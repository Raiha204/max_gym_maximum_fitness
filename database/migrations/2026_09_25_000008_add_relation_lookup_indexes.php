<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->index('member_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['membership_id', 'payment_date']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['membership_id', 'attendance_date']);
        });

        Schema::table('maintenance', function (Blueprint $table) {
            $table->index(['equipment_id', 'maintenance_date']);
        });
    }

    public function down(): void
    {
        Schema::table('maintenance', function (Blueprint $table) {
            $table->dropIndex(['equipment_id', 'maintenance_date']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['membership_id', 'attendance_date']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['membership_id', 'payment_date']);
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->dropIndex(['member_id']);
        });
    }
};
