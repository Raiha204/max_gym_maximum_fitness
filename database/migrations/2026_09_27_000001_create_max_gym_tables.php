<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('cashier')->after('email');
            });
        }

        if (!Schema::hasTable('memberships')) {
            Schema::create('memberships', function (Blueprint $table) {
                $table->id();
                $table->string('member_id', 12)->unique();
                $table->string('full_name');
                $table->string('email');
                $table->string('phone', 50);
                $table->string('gender', 20)->default('Male');
                $table->date('date_of_birth')->nullable();
                $table->string('photo_path')->nullable();
                $table->string('plan_type');
                $table->unsignedInteger('duration_months')->default(1);
                $table->decimal('monthly_rate', 10, 2)->default(650.00);
                $table->decimal('total_amount', 10, 2)->default(650.00);
                $table->string('payment_method', 30)->default('Cash');
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status', 30)->default('Active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('membership_id')->constrained('memberships')->cascadeOnDelete();
                $table->string('member_id', 12)->index();
                $table->timestamp('checked_in_at');
                $table->string('verification_method', 40)->default('QR Code Scan');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('membership_id')->nullable()->constrained('memberships')->nullOnDelete();
                $table->string('payer_name');
                $table->string('category')->default('Membership Registration');
                $table->string('plan_label')->nullable();
                $table->decimal('amount', 10, 2);
                $table->string('payment_method', 30)->default('Cash');
                $table->timestamp('paid_at');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('walk_ins')) {
            Schema::create('walk_ins', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_no', 30)->unique();
                $table->string('session_type', 40)->default('Regular Walk-In');
                $table->decimal('amount', 10, 2)->default(65.00);
                $table->string('payment_method', 30)->default('Cash');
                $table->timestamp('paid_at');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('equipment')) {
            Schema::create('equipment', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->nullable();
                $table->string('name');
                $table->string('category', 100);
                $table->string('status', 40)->default('Operational');
                $table->date('last_inspected')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('maintenance') && !Schema::hasTable('maintenances')) {
            Schema::create('maintenance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
                $table->string('equipment_name')->nullable();
                $table->string('category', 100)->nullable();
                $table->string('priority', 30)->default('Medium');
                $table->string('issue');
                $table->string('status', 30)->default('Open');
                $table->timestamp('reported_at');
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance');
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('walk_ins');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('memberships');
    }
};
