<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyMaxGymSchemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['attendances', 'payments', 'memberships'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('member_type')->default('regular');
            $table->string('plan_name')->default('Monthly');
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->date('payment_due_date')->nullable();
            $table->boolean('penalty_applied')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unsignedBigInteger('member_id')->nullable();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_id')->nullable();
            $table->string('visitor_type')->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->string('status')->default('completed');
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_id');
            $table->date('attendance_date');
            $table->time('check_in');
            $table->timestamps();
        });

        $migration = require __DIR__ . '/../../database/migrations/2026_10_02_000001_repair_legacy_max_gym_tables.php';
        $migration->up();
    }

    public function test_legacy_membership_schema_is_compatible_with_current_models(): void
    {
        $this->assertTrue(Schema::hasColumn('memberships', 'full_name'));
        $this->assertTrue(Schema::hasColumn('payments', 'payer_name'));
        $this->assertTrue(Schema::hasColumn('attendances', 'checked_in_at'));

        $membership = Membership::create([
            'member_id' => '133521',
            'full_name' => 'John Kelvin Navarro',
            'email' => 'j.navarro.555058@umindanao.edu.ph',
            'phone' => '09088184444',
            'gender' => 'Other',
            'date_of_birth' => '2005-01-15',
            'photo_path' => null,
            'plan_type' => 'Student Membership',
            'duration_months' => 2,
            'monthly_rate' => 650,
            'total_amount' => 1300,
            'payment_method' => 'Cash',
            'start_date' => '2026-10-02',
            'end_date' => '2026-12-02',
            'status' => 'Active',
        ]);

        $payment = Payment::create([
            'membership_id' => $membership->id,
            'payer_name' => $membership->full_name,
            'category' => 'Membership Registration',
            'plan_label' => 'Student Membership (2 Months)',
            'amount' => 1300,
            'payment_method' => 'Cash',
            'paid_at' => now(),
        ]);

        $this->assertNotNull($membership->id);
        $this->assertNotNull($payment->id);
    }
}
