<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $migrationNeedsRepair = false;

        foreach (['memberships', 'payments', 'attendances'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if ($table === 'memberships') {
                $migrationNeedsRepair = $migrationNeedsRepair
                    || !Schema::hasColumn('memberships', 'full_name')
                    || !Schema::hasColumn('memberships', 'plan_type')
                    || !Schema::hasColumn('memberships', 'monthly_rate');
                continue;
            }

            if ($table === 'payments') {
                $migrationNeedsRepair = $migrationNeedsRepair || !Schema::hasColumn('payments', 'payer_name');
                continue;
            }

            if ($table === 'attendances') {
                $migrationNeedsRepair = $migrationNeedsRepair || !Schema::hasColumn('attendances', 'checked_in_at');
            }
        }

        if (!$migrationNeedsRepair) {
            return;
        }

        $this->setForeignKeyChecks(false);

        try {
        if (Schema::hasTable('memberships') && (
            !Schema::hasColumn('memberships', 'full_name')
            || !Schema::hasColumn('memberships', 'plan_type')
            || !Schema::hasColumn('memberships', 'monthly_rate')
        )) {
            $legacyMemberships = DB::table('memberships')->get();

            Schema::create('memberships_new', function (Blueprint $table) {
                $table->id();
                $table->string('member_id', 12)->nullable()->unique();
                $table->string('full_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('gender', 20)->default('Male');
                $table->date('date_of_birth')->nullable();
                $table->string('photo_path')->nullable();
                $table->string('plan_type')->nullable();
                $table->unsignedInteger('duration_months')->default(1);
                $table->decimal('monthly_rate', 10, 2)->default(650.00);
                $table->decimal('total_amount', 10, 2)->default(650.00);
                $table->string('payment_method', 30)->default('Cash');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('status', 30)->default('Active');
                $table->timestamps();
            });

            foreach ($legacyMemberships as $membership) {
                $memberId = $membership->member_id ?? random_int(100000, 999999);
                $fullName = $membership->full_name ?? trim(implode(' ', array_filter([
                    $membership->first_name ?? null,
                    $membership->last_name ?? null,
                    $membership->member_name ?? null,
                ])));

                DB::table('memberships_new')->insert([
                    'id' => $membership->id,
                    'member_id' => (string) $memberId,
                    'full_name' => $fullName ?: 'Legacy Member',
                    'email' => $membership->email ?? null,
                    'phone' => $membership->phone ?? null,
                    'gender' => $membership->gender ?? 'Male',
                    'date_of_birth' => $membership->date_of_birth ?? null,
                    'photo_path' => $membership->photo_path ?? null,
                    'plan_type' => $membership->plan_type ?? $membership->plan_name ?? 'Regular Membership',
                    'duration_months' => (int) ($membership->duration_months ?? 1),
                    'monthly_rate' => (float) ($membership->monthly_rate ?? $membership->amount_due ?? 650),
                    'total_amount' => (float) ($membership->total_amount ?? $membership->amount_due ?? 650),
                    'payment_method' => $membership->payment_method ?? 'Cash',
                    'start_date' => $membership->start_date ?? now()->toDateString(),
                    'end_date' => $membership->end_date ?? now()->addMonth()->toDateString(),
                    'status' => $membership->status ?? 'Active',
                    'created_at' => $membership->created_at ?? now(),
                    'updated_at' => $membership->updated_at ?? now(),
                ]);
            }

            Schema::dropIfExists('memberships');
            Schema::rename('memberships_new', 'memberships');
        }

        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'payer_name')) {
            $legacyPayments = DB::table('payments')->get();

            Schema::create('payments_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('membership_id')->nullable();
                $table->string('payer_name')->nullable();
                $table->string('category')->default('Membership Registration');
                $table->string('plan_label')->nullable();
                $table->decimal('amount', 10, 2)->default(0);
                $table->string('payment_method', 30)->default('Cash');
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });

            foreach ($legacyPayments as $payment) {
                $membershipId = $payment->membership_id ?? null;
                $membership = $membershipId ? DB::table('memberships')->where('id', $membershipId)->first() : null;

                DB::table('payments_new')->insert([
                    'id' => $payment->id,
                    'membership_id' => $membershipId,
                    'payer_name' => $payment->payer_name ?? ($membership ? ($membership->full_name ?? 'Legacy Payer') : 'Legacy Payer'),
                    'category' => $payment->category ?? 'Membership Registration',
                    'plan_label' => $payment->plan_label ?? ($membership ? ($membership->plan_type ?? 'Membership') : ($payment->visitor_type ?? 'Membership')),
                    'amount' => (float) ($payment->amount ?? $payment->amount_due ?? 0),
                    'payment_method' => $payment->payment_method ?? 'Cash',
                    'paid_at' => $payment->paid_at ?? ($payment->payment_date ?? now()),
                    'created_at' => $payment->created_at ?? now(),
                    'updated_at' => $payment->updated_at ?? now(),
                ]);
            }

            Schema::dropIfExists('payments');
            Schema::rename('payments_new', 'payments');
        }

        if (Schema::hasTable('attendances') && !Schema::hasColumn('attendances', 'checked_in_at')) {
            $legacyAttendances = DB::table('attendances')->get();

            Schema::create('attendances_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('membership_id')->nullable();
                $table->string('member_id', 12)->nullable();
                $table->timestamp('checked_in_at')->nullable();
                $table->string('verification_method', 40)->default('QR Code Scan');
                $table->timestamps();
            });

            foreach ($legacyAttendances as $attendance) {
                $membershipId = $attendance->membership_id ?? null;
                $membership = $membershipId ? DB::table('memberships')->where('id', $membershipId)->first() : null;
                $combinedTime = $attendance->attendance_date ?? now()->toDateString();

                if (!empty($attendance->check_in)) {
                    $combinedTime = $attendance->attendance_date . ' ' . $attendance->check_in;
                }

                DB::table('attendances_new')->insert([
                    'id' => $attendance->id,
                    'membership_id' => $membershipId,
                    'member_id' => $attendance->member_id ?? ($membership ? $membership->member_id : null),
                    'checked_in_at' => $combinedTime,
                    'verification_method' => 'QR Code Scan',
                    'created_at' => $attendance->created_at ?? now(),
                    'updated_at' => $attendance->updated_at ?? now(),
                ]);
            }

            Schema::dropIfExists('attendances');
            Schema::rename('attendances_new', 'attendances');
        }

        } finally {
            $this->setForeignKeyChecks(true);
        }
    }

    private function setForeignKeyChecks(bool $enabled): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = '.($enabled ? 'ON' : 'OFF'));
        } elseif (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = '.($enabled ? '1' : '0'));
        }
    }

    public function down(): void
    {
        // Intentionally left empty because this repair is one-way and should be safe to keep.
    }
};
