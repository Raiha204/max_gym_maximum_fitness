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

        $loginResponse = $this->get(route('login'))->assertOk();
        $this->assertStringContainsString('no-store', $loginResponse->headers->get('Cache-Control'));

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('login'))->assertRedirect(route('dashboard'));

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
            route('payments.index'),
            route('reports.index'),
            route('equipment.index'),
            route('maintenance.index'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
        $this->get(route('payments.index', ['method' => 'Cash', 'date' => '2026-10-08']))
            ->assertOk()
            ->assertSee('Specific Date Lookup')
            ->assertSee('Membership Payments Ledger');
        $this->get(route('equipment.index'))
            ->assertOk()
            ->assertSee('ID #' . $equipment->id)
            ->assertSee('Equipment Name', false)
            ->assertSee('id="equipment-search"', false)
            ->assertSee('description, or ID', false)
            ->assertSee('Optional equipment details');
        $this->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee('ID #' . $maintenance->id)
            ->assertSee('id="maintenance-search"', false)
            ->assertSee('data-search="test treadmill test maintenance issue', false)
            ->assertSee('Maintenance Name')
            ->assertSee('Description');

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

    public function test_partial_renewal_holds_credit_until_existing_membership_expires(): void
    {
        $this->actingAs(User::factory()->create());
        Carbon::setTestNow(Carbon::parse('2026-10-08 12:00:00', 'UTC'));

        try {
            $membership = $this->createMembership([
                'start_date' => '2026-09-18',
                'end_date' => '2026-10-18',
            ]);
            Payment::create([
                'membership_id' => $membership->id,
                'payer_name' => $membership->full_name,
                'category' => 'Membership Registration',
                'plan_label' => 'Student Membership (1 Month)',
                'amount' => 650,
                'payment_method' => 'Cash',
                'paid_at' => Carbon::parse('2026-10-08 11:00:00', 'UTC'),
            ]);

            $this->post(route('memberships.renew', $membership), [
                'plan_type' => 'Student Membership',
                'duration_months' => 1,
                'amount_paid' => 600,
                'payment_method' => 'Cash',
            ])->assertRedirect(route('memberships.show', $membership));

            $membership = $membership->fresh();
            $this->assertSame('2026-10-18', $membership->daily_credit_paused_until->toDateString());
            $this->assertSame('2026-11-18', $membership->end_date->toDateString());
            $this->assertSame('Partial', $membership->membership_status);
            $this->assertTrue($membership->can_check_in);
            $this->get(route('attendance.verify', ['q' => $membership->member_id]))
                ->assertOk()
                ->assertJsonPath('status', 'Partial')
                ->assertJsonPath('can_check_in', true);
            $this->post(route('attendance.store'), [
                'member_id' => $membership->member_id,
                'verification_method' => 'Manual Search',
            ])->assertRedirect(route('attendance.index', ['member_id' => $membership->member_id]));

            Carbon::setTestNow(Carbon::parse('2026-10-18 12:00:00', 'UTC'));
            $this->assertSame(600.0, $membership->fresh()->available_credit);
            $this->assertSame('Partial', $membership->fresh()->membership_status);
            $this->get(route('memberships.show', $membership))
                ->assertOk()
                ->assertSee('credit is held')
                ->assertSee('October 18, 2026');

            Carbon::setTestNow(Carbon::parse('2026-10-19 12:00:00', 'UTC'));
            $this->assertSame(550.0, $membership->fresh()->available_credit);
            $this->assertSame('Partial', $membership->fresh()->membership_status);
        } finally {
            Carbon::setTestNow();
        }
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
            $this->assertSame('Partial', $membership->membership_status);
            $this->assertDatabaseHas('payments', [
                'membership_id' => $membership->id,
                'category' => 'Partial Payment',
                'plan_label' => 'Student Membership (1 Month)',
                'amount' => 600,
            ]);

            Carbon::setTestNow(Carbon::parse('2026-10-09 12:00:00', 'UTC'));
            $membership = $membership->fresh();
            $this->assertSame(550.0, $membership->available_credit);
            $this->assertSame('Partial', $membership->membership_status);
            $this->get(route('memberships.show', $membership))
                ->assertOk()
                ->assertSee('Partial')
                ->assertSee('Available Credit')
                ->assertSee('₱550.00')
                ->assertSee('₱50.00')
                ->assertSee('Partial Payment')
                ->assertSee('Student Membership (1 Month)');
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
            $this->assertSame('Active', $membership->membership_status);
            $this->get(route('memberships.show', $membership))
                ->assertOk()
                ->assertDontSee('Available Credit')
                ->assertDontSee('Unpaid Plan Balance');
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

    public function test_revenue_ledger_search_filters_sorting_and_pagination_work(): void
    {
        $this->actingAs(User::factory()->create());
        $membership = $this->createMembership([
            'member_id' => '765432',
            'full_name' => 'Ledger, Member',
        ]);

        for ($i = 1; $i <= 32; $i++) {
            Payment::create([
                'membership_id' => $membership->id,
                'payer_name' => $i === 32 ? 'Special Search Member' : 'Ledger Member',
                'category' => 'Membership Registration',
                'plan_label' => 'Student Membership (1 Month)',
                'amount' => $i,
                'payment_method' => $i === 32 ? 'GCash' : 'Cash',
                'paid_at' => Carbon::parse('2026-10-08 01:00:00', 'UTC')->addMinutes($i),
            ]);
        }

        foreach ([['WALK-001', 'Cash', 5], ['WALK-002', 'GCash', 40], ['WALK-003', 'Cash', 60]] as [$receipt, $method, $amount]) {
            WalkIn::create([
                'receipt_no' => $receipt,
                'first_name' => 'Walk',
                'last_name' => 'Customer',
                'session_type' => 'Regular Walk-In',
                'amount' => $amount,
                'payment_method' => $method,
                'paid_at' => Carbon::parse('2026-10-08 04:00:00', 'UTC'),
            ]);
        }

        $defaultPage = $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'payment_method' => 'GCash',
            'source' => 'Walk-In',
        ]))
            ->assertOk()
            ->assertSee('Showing 1 to 1 of 1 entries')
            ->assertSee('name="rows"', false);

        $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
        ]))
            ->assertOk()
            ->assertSee('Showing 1 to 10 of 35 entries');

        $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'rows' => 100,
        ]))
            ->assertOk()
            ->assertSee('Showing 1 to 35 of 35 entries');

        $firstPage = $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'rows' => 30,
            'payment_method' => 'All',
            'source' => 'All',
            'sort' => 'date_asc',
        ]))
            ->assertOk()
            ->assertSee('Showing 1 to 30 of 35 entries')
            ->assertSee('data-ledger-key="payment-1"', false)
            ->assertDontSee('data-ledger-key="payment-32"', false);
        $this->assertStringContainsString('page=2', $firstPage->getContent());

        $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'rows' => 30,
            'payment_method' => 'All',
            'source' => 'All',
            'sort' => 'date_asc',
            'page' => 2,
        ]))
            ->assertOk()
            ->assertSee('Showing 31 to 35 of 35 entries')
            ->assertSee('data-ledger-key="payment-32"', false);

        $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'search' => 'Special Search',
            'source' => 'Membership',
        ]))
            ->assertOk()
            ->assertSee('Special Search Member')
            ->assertSee('Showing 1 to 1 of 1 entries');

        $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'search' => '765432',
            'payment_method' => 'GCash',
            'source' => 'Membership',
        ]))
            ->assertOk()
            ->assertSee('Special Search Member')
            ->assertSee('Showing 1 to 1 of 1 entries')
            ->assertDontSee('WALK-002');

        $amountSortedResponse = $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'sort' => 'amount_asc',
        ]))->assertOk();
        $amountSortedHtml = $amountSortedResponse->getContent();
        $this->assertLessThan(
            strpos($amountSortedHtml, 'data-ledger-key="walk-in-1"'),
            strpos($amountSortedHtml, 'data-ledger-key="payment-1"')
        );

        $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'source' => 'Walk-In',
            'payment_method' => 'GCash',
        ]))
            ->assertOk()
            ->assertSee('Showing 1 to 1 of 1 entries');
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

    public function test_revenue_ledger_search_matches_membership_and_walk_in_names(): void
    {
        $this->actingAs(User::factory()->create());
        $membership = $this->createMembership([
            'member_id' => '246810',
            'full_name' => 'Gilbuena, Carl Adrian G.',
        ]);

        Payment::create([
            'membership_id' => $membership->id,
            'payer_name' => 'Carl Adrian G. Gilbuena',
            'category' => 'Membership Registration',
            'plan_label' => 'Student Membership (1 Month)',
            'amount' => 650,
            'payment_method' => 'Cash',
            'paid_at' => Carbon::parse('2026-10-08 04:00:00', 'UTC'),
        ]);
        WalkIn::create([
            'receipt_no' => 'WALK-CARL-001',
            'first_name' => 'Carl Adrian G.',
            'last_name' => 'Walk-In',
            'session_type' => 'Regular Walk-In',
            'amount' => 65,
            'payment_method' => 'Cash',
            'paid_at' => Carbon::parse('2026-10-08 04:00:00', 'UTC'),
        ]);

        $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'search' => 'Carl Adrian G. Gilbuena',
            'source' => 'Membership',
        ]))
            ->assertOk()
            ->assertSee('Gilbuena, Carl Adrian G.')
            ->assertSee('Showing 1 to 1 of 1 entries');

        $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
            'search' => 'Carl Adrian G.',
            'source' => 'Walk-In',
        ]))
            ->assertOk()
            ->assertSee('Carl Adrian G. Walk-In')
            ->assertSee('Showing 1 to 1 of 1 entries');

        $this->get(route('reports.index', [
            'period' => 'date',
            'date' => '2026-10-08',
        ]))
            ->assertOk()
            ->assertDontSee('>Apply</button>', false)
            ->assertSee("params.set('q', query)", false)
            ->assertSee('Select one to view.', false)
            ->assertSee("select.addEventListener('change', () => form.requestSubmit())", false);
    }

    public function test_member_and_revenue_search_suggestions_match_names_and_ids(): void
    {
        $this->actingAs(User::factory()->create());
        $membership = $this->createMembership([
            'member_id' => '62884',
            'full_name' => 'Gilbuena, Carl Adrian G.',
        ]);
        Payment::create([
            'membership_id' => $membership->id,
            'payer_name' => 'Carl Adrian G. Gilbuena',
            'category' => 'Membership Registration',
            'plan_label' => 'Student Membership (1 Month)',
            'amount' => 650,
            'payment_method' => 'Cash',
            'paid_at' => Carbon::parse('2026-10-08 04:00:00', 'UTC'),
        ]);
        WalkIn::create([
            'receipt_no' => 'WALK-CARL-001',
            'first_name' => 'Carl Adrian',
            'last_name' => 'Guest',
            'session_type' => 'Regular Walk-In',
            'amount' => 65,
            'payment_method' => 'Cash',
            'paid_at' => Carbon::parse('2026-10-08 04:00:00', 'UTC'),
        ]);

        $this->getJson(route('memberships.search', ['q' => 'Carl Adrian G. Gilbuena']))
            ->assertOk()
            ->assertJsonPath('members.0.member_id', '62884')
            ->assertJsonPath('members.0.full_name', 'Gilbuena, Carl Adrian G.');

        $this->get(route('memberships.index', ['search' => 'Carl Adrian G. Gilbuena']))
            ->assertOk()
            ->assertSee('62884')
            ->assertSee('Gilbuena')
            ->assertSee('Carl Adrian G.');

        $this->getJson(route('reports.search', [
            'q' => 'Carl Adrian G. Gilbuena',
            'period' => 'date',
            'date' => '2026-10-08',
        ]))
            ->assertOk()
            ->assertJsonPath('matches.0.identifier', '62884')
            ->assertJsonPath('matches.0.search_value', '62884');

        $this->getJson(route('reports.search', [
            'q' => 'Carl Adrian Guest',
            'period' => 'date',
            'date' => '2026-10-08',
        ]))
            ->assertOk()
            ->assertJsonPath('matches.0.identifier', 'WALK-CARL-001')
            ->assertJsonPath('matches.0.search_value', 'WALK-CARL-001');
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
            'notes' => 'Original equipment description',
        ])->assertRedirect();
        $equipment = Equipment::where('name', 'Workflow test equipment')->firstOrFail();
        $this->assertSame('Original equipment description', $equipment->notes);

        $this->put(route('equipment.update', $equipment), [
            'name' => 'Updated workflow equipment',
            'notes' => 'Updated equipment description',
        ])->assertRedirect();
        $equipment->refresh();
        $this->assertSame('Updated workflow equipment', $equipment->name);
        $this->assertSame('Updated equipment description', $equipment->notes);
        $this->assertSame('Cardio', $equipment->category);
        $this->assertSame('Operational', $equipment->status);

        $this->post(route('maintenance.store'), [
            'equipment_id' => (string) $equipment->id,
            'custom_equipment_name' => '',
            'category' => 'Cardio',
            'priority' => 'High',
            'issue' => 'Workflow test issue',
        ])->assertRedirect();
        $maintenance = Maintenance::where('equipment_id', $equipment->id)->firstOrFail();
        $this->assertSame('Under Maintenance', $equipment->fresh()->status);

        $this->put(route('maintenance.update', $maintenance), [
            'equipment_name' => 'Updated maintenance name',
            'issue' => 'Updated maintenance description',
        ])->assertRedirect();
        $maintenance->refresh();
        $this->assertSame('Updated maintenance name', $maintenance->equipment_name);
        $this->assertSame('Updated maintenance description', $maintenance->issue);
        $this->assertSame('Open', $maintenance->status);

        $this->patch(route('maintenance.status', $maintenance), [
            'status' => 'In Progress',
        ])->assertRedirect();
        $this->assertSame('In Progress', $maintenance->fresh()->status);
        $this->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee('Mark Resolved');

        $this->post(route('maintenance.store'), [
            'equipment_id' => (string) $equipment->id,
            'custom_equipment_name' => '',
            'category' => 'Cardio',
            'priority' => 'Medium',
            'issue' => 'Second workflow issue',
        ])->assertRedirect();
        $secondMaintenance = Maintenance::where('issue', 'Second workflow issue')->firstOrFail();

        $this->patch(route('maintenance.status', $maintenance), [
            'status' => 'Resolved',
        ])->assertRedirect();
        $this->assertSame('Resolved', $maintenance->fresh()->status);
        $this->assertNotNull($maintenance->fresh()->resolved_at);
        $this->assertSame('Under Maintenance', $equipment->fresh()->status);
        $this->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee('Mark as In Progress')
            ->assertSee('data-maintenance-filter="Resolved"', false)
            ->assertSee('data-status="Resolved"', false)
            ->assertSee('Reported')
            ->assertSee('Completed')
            ->assertSee('Updated maintenance description')
            ->assertSee('Updated maintenance name')
            ->assertSee($maintenance->fresh()->resolved_at->format('M j, Y'));

        $this->patch(route('maintenance.status', $maintenance), [
            'status' => 'In Progress',
        ])->assertRedirect();
        $this->assertNull($maintenance->fresh()->resolved_at);
        $this->assertSame('Under Maintenance', $equipment->fresh()->status);

        $this->patch(route('maintenance.resolve', $maintenance))->assertRedirect();
        $this->assertSame('Resolved', $maintenance->fresh()->status);
        $this->assertNotNull($maintenance->fresh()->resolved_at);
        $this->assertSame('Under Maintenance', $equipment->fresh()->status);

        $this->patch(route('maintenance.status', $secondMaintenance), [
            'status' => 'Resolved',
        ])->assertRedirect();
        $this->assertSame('Operational', $equipment->fresh()->status);

        $this->delete(route('maintenance.destroy', $maintenance))->assertRedirect();
        $this->delete(route('maintenance.destroy', $secondMaintenance))->assertRedirect();
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
