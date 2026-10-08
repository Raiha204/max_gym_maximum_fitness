<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_authenticated_staff_can_open_the_main_application_pages(): void
    {
        $user = User::factory()->create();
        $membership = $this->createMembership();
        $equipment = Equipment::create([
            'name' => 'Test treadmill',
            'category' => 'Cardio',
            'status' => 'Operational',
            'last_inspected' => today(),
        ]);
        $maintenance = Maintenance::create([
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name,
            'category' => $equipment->category,
            'priority' => 'Medium',
            'issue' => 'Test maintenance issue',
            'status' => 'Open',
            'reported_at' => now(),
        ]);

        $this->actingAs($user);
        $urls = [
            route('dashboard'),
            route('attendance.index'),
            route('memberships.index'),
            route('memberships.create'),
            route('memberships.show', $membership),
            route('memberships.edit', $membership),
            route('walk-ins.index'),
            route('reports.index'),
            route('equipment.index'),
            route('maintenance.index'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('attendance.search', ['q' => 'Member']))
            ->assertOk()
            ->assertJsonPath('members.0.member_id', $membership->member_id);
        $this->get(route('attendance.verify', ['q' => $membership->member_id]))
            ->assertOk()
            ->assertJsonPath('found', true);
        $this->assertNotNull($maintenance->id);
    }

    public function test_member_registration_photo_update_and_renewal_workflows(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->post(route('memberships.store'), [
            'surname' => 'Example',
            'first_name' => 'Member',
            'email' => 'example.member@example.test',
            'phone' => '09123456789',
            'gender' => 'Other',
            'date_of_birth' => '2000-01-15',
            'photo' => UploadedFile::fake()->create('member.png', 20, 'image/png'),
            'plan_type' => 'Student Membership',
            'duration_months' => 1,
            'amount_paid' => 650,
            'payment_method' => 'Cash',
        ])->assertRedirect();

        $membership = Membership::where('email', 'example.member@example.test')->firstOrFail();
        $this->assertSame('Example, Member', $membership->full_name);
        $this->assertSame(650.0, $membership->total_amount);
        Storage::disk('public')->assertExists($membership->photo_path);
        $this->assertDatabaseHas('payments', [
            'membership_id' => $membership->id,
            'amount' => 650,
            'payment_method' => 'Cash',
        ]);

        $this->put(route('memberships.update', $membership), [
            'surname' => 'Updated',
            'first_name' => 'Member',
            'email' => 'example.member@example.test',
            'phone' => '09123456789',
            'gender' => 'Other',
            'date_of_birth' => '2000-01-15',
        ])->assertRedirect(route('memberships.show', $membership));
        $this->assertSame('Updated, Member', $membership->fresh()->full_name);

        $this->post(route('memberships.renew', $membership), [
            'plan_type' => 'Regular Membership',
            'duration_months' => 3,
            'amount_paid' => 2250,
            'payment_method' => 'GCash',
        ])->assertRedirect(route('memberships.show', $membership));
        $this->assertSame('Regular Membership', $membership->fresh()->plan_type);
        $this->assertDatabaseHas('payments', [
            'membership_id' => $membership->id,
            'amount' => 2250,
            'payment_method' => 'GCash',
        ]);
    }

    public function test_partial_membership_payment_uses_daily_credit_and_accepts_balance_payment(): void
    {
        $this->actingAs(User::factory()->create());
        Carbon::setTestNow(Carbon::parse('2026-10-08 12:00:00', 'UTC'));

        try {
            $this->post(route('memberships.store'), [
                'surname' => 'Partial',
                'first_name' => 'Member',
                'email' => 'partial.member@example.test',
                'phone' => '09123456789',
                'gender' => 'Other',
                'date_of_birth' => '2000-01-15',
                'plan_type' => 'Student Membership',
                'duration_months' => 1,
                'amount_paid' => 600,
                'payment_method' => 'Cash',
            ])->assertRedirect();

            $membership = Membership::where('email', 'partial.member@example.test')->firstOrFail();
            $this->assertTrue($membership->daily_credit_enabled);
            $this->assertSame(50.0, $membership->remaining_amount);
            $this->assertSame(600.0, $membership->available_credit);

            Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', 'UTC'));
            $membership = $membership->fresh();
            $this->assertSame(550.0, $membership->available_credit);
            $this->assertSame('Partial', $membership->membership_status);
            $this->get(route('memberships.show', $membership))
                ->assertOk()
                ->assertSee('Partial')
                ->assertSee('Available Credit')
                ->assertSee('₱550.00')
                ->assertSee('₱50.00');
            $this->get(route('memberships.index', ['status' => 'partial']))
                ->assertOk()
                ->assertSee($membership->member_id);
            $this->get(route('attendance.verify', ['q' => $membership->member_id]))
                ->assertOk()
                ->assertJsonPath('status', 'Partial')
                ->assertJsonPath('can_check_in', true);
            $this->post(route('attendance.store'), [
                'member_id' => $membership->member_id,
                'verification_method' => 'Manual Search',
            ])->assertRedirect(route('attendance.index', ['member_id' => $membership->member_id]));

            $this->post(route('memberships.payments.store', $membership), [
                'amount_paid' => 50,
                'payment_method' => 'GCash',
            ])->assertRedirect(route('memberships.show', $membership));

            $membership = $membership->fresh();
            $this->assertSame(0.0, $membership->remaining_amount);
            $this->assertSame(600.0, $membership->available_credit);
            $this->assertDatabaseHas('payments', [
                'membership_id' => $membership->id,
                'category' => 'Membership Balance Payment',
                'amount' => 50,
                'payment_method' => 'GCash',
            ]);

            Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'UTC'));
            $this->assertSame(0.0, $membership->fresh()->available_credit);
            $this->assertSame('Expired', $membership->fresh()->membership_status);
            $this->get(route('attendance.verify', ['q' => $membership->member_id]))
                ->assertOk()
                ->assertJsonPath('status', 'Expired');
            $this->post(route('attendance.store'), [
                'member_id' => $membership->member_id,
                'verification_method' => 'Manual Search',
            ])->assertSessionHasErrors('expired');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_revenue_report_uses_manila_date_and_time_for_utc_payment_timestamps(): void
    {
        $this->actingAs(User::factory()->create());
        $membership = $this->createMembership(['member_id' => '123456']);
        Payment::create([
            'membership_id' => $membership->id,
            'payer_name' => $membership->full_name,
            'category' => 'Membership Registration',
            'plan_label' => 'Student Membership (1 Month)',
            'amount' => 650,
            'payment_method' => 'Cash',
            'paid_at' => Carbon::parse('2026-10-07 17:30:00', 'UTC'),
        ]);

        $this->get(route('reports.index', ['date' => '2026-10-08']))
            ->assertOk()
            ->assertSee('08/10/2026')
            ->assertSee('1:30 AM');
    }

    public function test_yearly_revenue_report_lists_combined_totals_for_each_month_of_selected_year(): void
    {
        $this->actingAs(User::factory()->create());
        $membership = $this->createMembership(['member_id' => '123456']);
        foreach ([
            ['2026-01-15 08:00:00', 650],
            ['2026-02-15 08:00:00', 500],
        ] as [$paidAt, $amount]) {
            Payment::create([
                'membership_id' => $membership->id,
                'payer_name' => $membership->full_name,
                'category' => 'Membership Registration',
                'plan_label' => 'Student Membership (1 Month)',
                'amount' => $amount,
                'payment_method' => 'Cash',
                'paid_at' => Carbon::parse($paidAt, 'UTC'),
            ]);
        }
        WalkIn::create([
            'receipt_no' => 'TEST-MONTHLY-001',
            'session_type' => 'Regular Walk-In',
            'amount' => 65,
            'payment_method' => 'Cash',
            'paid_at' => Carbon::parse('2026-01-20 08:00:00', 'UTC'),
        ]);

        $this->get(route('reports.index', ['period' => 'year', 'year' => '2026']))
            ->assertOk()
            ->assertSee('Yearly Revenue by Month')
            ->assertSee('Total revenue for January')
            ->assertSee('715.00')
            ->assertSee('Total revenue for February')
            ->assertSee('500.00')
            ->assertSee('Total Revenue for 2026')
            ->assertDontSee('Membership Payments</div>')
            ->assertDontSee('Walk-In Revenue</div>');
    }

    public function test_monthly_revenue_report_shows_only_the_selected_month(): void
    {
        $this->actingAs(User::factory()->create());
        $membership = $this->createMembership(['member_id' => '123456']);
        foreach ([
            ['2026-01-15 08:00:00', 650],
            ['2026-02-15 08:00:00', 500],
        ] as [$paidAt, $amount]) {
            Payment::create([
                'membership_id' => $membership->id,
                'payer_name' => $membership->full_name,
                'category' => 'Membership Registration',
                'plan_label' => 'Student Membership (1 Month)',
                'amount' => $amount,
                'payment_method' => 'Cash',
                'paid_at' => Carbon::parse($paidAt, 'UTC'),
            ]);
        }

        $this->get(route('reports.index', ['period' => 'month', 'month' => '2026-02']))
            ->assertOk()
            ->assertSee('Monthly Revenue')
            ->assertSee('February 2026')
            ->assertSee('500.00')
            ->assertDontSee('Monthly revenue totals')
            ->assertDontSee('650.00');
    }

    public function test_membership_phone_number_requires_exactly_eleven_digits(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('memberships.store'), [
            'surname' => 'Example',
            'first_name' => 'Member',
            'email' => 'invalid.phone@example.test',
            'phone' => '0917482991',
            'gender' => 'Other',
            'date_of_birth' => '2000-01-15',
            'plan_type' => 'Student Membership',
            'duration_months' => 1,
            'amount_paid' => 650,
            'payment_method' => 'Cash',
        ])->assertSessionHasErrors('phone');
    }

    public function test_membership_registration_payment_time_displays_in_manila_time(): void
    {
        $this->actingAs(User::factory()->create());
        Carbon::setTestNow(Carbon::parse('2026-10-08 03:28:00', 'UTC'));

        try {
            $this->post(route('memberships.store'), [
                'surname' => 'Example',
                'first_name' => 'Member',
                'email' => 'local.time.member@example.test',
                'phone' => '09123456789',
                'gender' => 'Other',
                'date_of_birth' => '2000-01-15',
                'plan_type' => 'Student Membership',
                'duration_months' => 1,
                'amount_paid' => 650,
                'payment_method' => 'Cash',
            ])->assertRedirect();

            $membership = Membership::where('email', 'local.time.member@example.test')->firstOrFail();
            $this->get(route('memberships.show', $membership))
                ->assertOk()
                ->assertSee('Oct 8, 2026 11:28 AM');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_member_pass_shows_total_attendance_check_ins(): void
    {
        $this->actingAs(User::factory()->create());
        $membership = $this->createMembership(['member_id' => '123456']);
        $membership->attendances()->create([
            'member_id' => $membership->member_id,
            'checked_in_at' => now(),
            'verification_method' => 'Manual Search',
        ]);

        $this->get(route('memberships.show', $membership))
            ->assertOk()
            ->assertSee('Total Check-Ins: 1');
    }

    public function test_attendance_search_verify_and_expiration_rules_work(): void
    {
        $this->actingAs(User::factory()->create());
        $active = $this->createMembership(['member_id' => '123456']);
        $expired = $this->createMembership([
            'member_id' => '654321',
            'full_name' => 'Expired, Member',
            'end_date' => Carbon::yesterday()->toDateString(),
        ]);

        $this->get(route('attendance.search', ['q' => '123456']))
            ->assertOk()
            ->assertJsonPath('members.0.member_id', '123456');
        $this->get(route('attendance.verify', ['q' => "MAX GYM MEMBER Member ID: {$active->member_id}"]))
            ->assertOk()
            ->assertJsonPath('member_id', '123456')
            ->assertJsonPath('date_of_birth', '01/01/2000')
            ->assertJsonPath('status', 'Active');
        $this->post(route('attendance.store'), [
            'member_id' => $active->member_id,
            'verification_method' => 'Manual Search',
        ])->assertRedirect(route('attendance.index', ['member_id' => $active->member_id]));
        $this->assertDatabaseHas('attendances', [
            'membership_id' => $active->id,
            'verification_method' => 'Manual Search',
        ]);

        $this->get(route('attendance.verify', ['q' => $expired->member_id]))
            ->assertOk()
            ->assertJsonPath('status', 'Expired');
        $this->from(route('attendance.index'))
            ->post(route('attendance.store'), [
                'member_id' => $expired->member_id,
                'verification_method' => 'QR Code Scan',
            ])
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('expired');
        $this->assertSame(1, Attendance::count());
    }

    public function test_attendance_allows_only_one_check_in_per_member_per_manila_day(): void
    {
        $this->actingAs(User::factory()->create());
        $membership = $this->createMembership([
            'member_id' => '123456',
            'end_date' => '2026-11-30',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-10-07 16:30:00', 'UTC'));
        try {
            $this->post(route('attendance.store'), [
                'member_id' => $membership->member_id,
                'verification_method' => 'Manual Search',
            ])->assertRedirect(route('attendance.index', ['member_id' => $membership->member_id]));

            $this->get(route('attendance.verify', ['q' => $membership->member_id]))
                ->assertOk()
                ->assertJsonPath('checked_in_today', true)
                ->assertJsonPath('checked_in_at', '12:30:00 AM');

            Carbon::setTestNow(Carbon::parse('2026-10-08 15:59:00', 'UTC'));
            $this->post(route('attendance.store'), [
                'member_id' => $membership->member_id,
                'verification_method' => 'QR Code Scan',
            ])
                ->assertRedirect(route('attendance.index', ['member_id' => $membership->member_id]))
                ->assertSessionHas('popup.title', 'Already Checked In')
                ->assertSessionHas('popup.message', 'Member, Test (ID: 123456) has already checked in today.');
            $this->assertSame(1, Attendance::count());

            Carbon::setTestNow(Carbon::parse('2026-10-08 16:00:00', 'UTC'));
            $this->post(route('attendance.store'), [
                'member_id' => $membership->member_id,
                'verification_method' => 'QR Code Scan',
            ])->assertRedirect(route('attendance.index', ['member_id' => $membership->member_id]));
            $this->assertSame(2, Attendance::count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_attendance_log_deduplicates_daily_rows_and_shows_manila_time(): void
    {
        $this->actingAs(User::factory()->create());
        $membership = $this->createMembership(['member_id' => '123456']);
        foreach (['2026-10-07 16:35:00', '2026-10-07 17:35:00'] as $timestamp) {
            $membership->attendances()->create([
                'member_id' => $membership->member_id,
                'checked_in_at' => Carbon::parse($timestamp, 'UTC'),
                'verification_method' => 'Manual Search',
            ]);
        }

        $this->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Total Check-Ins: 1')
            ->assertSee('October 8, 2026')
            ->assertSee('1:35:00 AM');
    }

    public function test_walk_in_fees_and_equipment_maintenance_actions_work(): void
    {
        $this->actingAs(User::factory()->create());

        Carbon::setTestNow(Carbon::parse('2026-10-08 03:28:00', 'UTC'));
        try {
            $this->post(route('walk-ins.store'), [
                'first_name' => 'Walk',
                'last_name' => 'In',
                'session_type' => 'Student Walk-In',
                'amount' => 1,
                'payment_method' => 'GCash',
            ])->assertRedirect();
            $this->assertDatabaseHas('walk_ins', [
                'session_type' => 'Student Walk-In',
                'amount' => 50,
                'payment_method' => 'GCash',
            ]);
            $this->get(route('walk-ins.index'))
                ->assertOk()
                ->assertSee('2026-10-08 11:28 AM');
        } finally {
            Carbon::setTestNow();
        }

        $this->post(route('equipment.store'), [
            'name' => 'Workflow test equipment',
            'category' => 'Cardio',
            'status' => 'Operational',
        ])->assertRedirect();
        $equipment = Equipment::where('name', 'Workflow test equipment')->firstOrFail();

        $this->post(route('maintenance.store'), [
            'equipment_id' => (string) $equipment->id,
            'custom_equipment_name' => '',
            'category' => 'Cardio',
            'priority' => 'High',
            'issue' => 'Workflow test issue',
        ])->assertRedirect();
        $maintenance = Maintenance::where('equipment_id', $equipment->id)->firstOrFail();
        $this->assertSame('Under Maintenance', $equipment->fresh()->status);

        $this->patch(route('maintenance.status', $maintenance), [
            'status' => 'In Progress',
        ])->assertRedirect();
        $this->assertSame('In Progress', $maintenance->fresh()->status);

        $this->patch(route('maintenance.resolve', $maintenance))->assertRedirect();
        $this->assertSame('Resolved', $maintenance->fresh()->status);
        $this->assertSame('Operational', $equipment->fresh()->status);

        $this->delete(route('maintenance.destroy', $maintenance))->assertRedirect();
        $this->put(route('equipment.update', $equipment), [
            'status' => 'Out of Order',
        ])->assertRedirect();
        $this->assertSame('Out of Order', $equipment->fresh()->status);
        $this->delete(route('equipment.destroy', $equipment))->assertRedirect();
        $this->assertDatabaseMissing('equipment', ['id' => $equipment->id]);
    }

    private function createMembership(array $attributes = []): Membership
    {
        return Membership::create(array_merge([
            'member_id' => '111111',
            'full_name' => 'Member, Test',
            'email' => 'member@example.test',
            'phone' => '09123456789',
            'gender' => 'Other',
            'date_of_birth' => '2000-01-01',
            'plan_type' => 'Student Membership',
            'duration_months' => 1,
            'monthly_rate' => 650,
            'total_amount' => 650,
            'payment_method' => 'Cash',
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'status' => 'Active',
        ], $attributes));
    }
}
