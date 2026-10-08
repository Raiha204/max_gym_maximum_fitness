<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', 'cashier@maxgym.test')->delete();

        User::updateOrCreate(
            ['email' => 'admin@maxgym.test'],
            ['name' => 'MAX Gym Admin', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        $carl = Membership::updateOrCreate(
            ['member_id' => '622884'],
            [
                'full_name' => 'Carl Adrian G. Gilbuena',
                'email' => 'gilbuenacarladrian12@gmail.com',
                'phone' => '0917-482-9910',
                'gender' => 'Male',
                'date_of_birth' => '2005-01-15',
                'plan_type' => 'Student Membership',
                'duration_months' => 1,
                'monthly_rate' => 650,
                'total_amount' => 650,
                'payment_method' => 'Cash',
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today()->addDays(30),
                'status' => 'Active',
            ]
        );

        $sophia = Membership::updateOrCreate(
            ['member_id' => '419205'],
            [
                'full_name' => 'Sophia Marie L. Reyes',
                'email' => 'sophia.reyes@gmail.com',
                'phone' => '0918-334-1120',
                'gender' => 'Female',
                'date_of_birth' => '2002-06-22',
                'plan_type' => 'Regular Membership',
                'duration_months' => 3,
                'monthly_rate' => 750,
                'total_amount' => 2250,
                'payment_method' => 'GCash',
                'start_date' => Carbon::today()->subDays(5),
                'end_date' => Carbon::today()->addDays(85),
                'status' => 'Active',
            ]
        );

        $marco = Membership::updateOrCreate(
            ['member_id' => '583921'],
            [
                'full_name' => 'Marco Antonio D. Santos',
                'email' => 'marco.santos@gmail.com',
                'phone' => '0927-819-3024',
                'gender' => 'Male',
                'date_of_birth' => '1995-11-08',
                'plan_type' => 'Regular Membership',
                'duration_months' => 1,
                'monthly_rate' => 750,
                'total_amount' => 750,
                'payment_method' => 'Cash',
                'start_date' => Carbon::today()->subDays(45),
                'end_date' => Carbon::today()->subDays(15),
                'status' => 'Expired',
            ]
        );

        // Payments (Today & September 27, 2026)
        Payment::updateOrCreate(
            ['membership_id' => $carl->id, 'category' => 'Membership Registration'],
            [
                'payer_name' => $carl->full_name,
                'plan_label' => 'Student Membership · 1 Month',
                'amount' => 650,
                'payment_method' => 'Cash',
                'paid_at' => Carbon::parse('2026-09-27 08:30:00'),
            ]
        );

        Payment::updateOrCreate(
            ['membership_id' => $sophia->id, 'category' => 'Membership Registration'],
            [
                'payer_name' => $sophia->full_name,
                'plan_label' => 'Regular Membership · 3 Months',
                'amount' => 2250,
                'payment_method' => 'GCash',
                'paid_at' => now()->subHours(2),
            ]
        );

        // Walk-Ins (Today & September 27, 2026)
        WalkIn::updateOrCreate(
            ['receipt_no' => 'WI-2026-201'],
            [
                'session_type' => 'Regular Walk-In',
                'amount' => 65,
                'payment_method' => 'Cash',
                'paid_at' => Carbon::parse('2026-09-27 09:15:00'),
            ]
        );

        WalkIn::updateOrCreate(
            ['receipt_no' => 'WI-2026-202'],
            [
                'session_type' => 'Student Walk-In',
                'amount' => 50,
                'payment_method' => 'GCash',
                'paid_at' => Carbon::parse('2026-09-27 14:20:00'),
            ]
        );

        WalkIn::updateOrCreate(
            ['receipt_no' => 'WI-2026-203'],
            [
                'session_type' => 'Regular Walk-In',
                'amount' => 65,
                'payment_method' => 'Cash',
                'paid_at' => now()->subHour(),
            ]
        );

        Attendance::firstOrCreate(
            ['membership_id' => $carl->id, 'member_id' => $carl->member_id],
            [
                'checked_in_at' => now()->subMinutes(35),
                'verification_method' => 'QR Code Scan',
            ]
        );

        $treadmill = Equipment::updateOrCreate(
            ['name' => 'Commercial Motorized Treadmill #1'],
            ['category' => 'Cardio', 'status' => 'Operational', 'last_inspected' => today()]
        );

        $latPulldown = Equipment::updateOrCreate(
            ['name' => 'Lat Pulldown & Seated Row Station'],
            ['category' => 'Strength Machines', 'status' => 'Under Maintenance', 'last_inspected' => today()]
        );

        Equipment::updateOrCreate(
            ['name' => 'Olympic Hex Dumbbell Rack (5–100 lbs)'],
            ['category' => 'Free Weights', 'status' => 'Operational', 'last_inspected' => today()]
        );

        Equipment::updateOrCreate(
            ['name' => 'Olympic Power Squat Cage #1'],
            ['category' => 'Benches & Racks', 'status' => 'Operational', 'last_inspected' => today()]
        );

        Equipment::updateOrCreate(
            ['name' => 'Battle Ropes & Kettlebell Station'],
            ['category' => 'Functional & Accessories', 'status' => 'Operational', 'last_inspected' => today()]
        );

        Maintenance::updateOrCreate(
            ['equipment_id' => $latPulldown->id, 'issue' => 'Primary pulley cable fraying near top weight stack'],
            [
                'equipment_name' => $latPulldown->name,
                'category' => 'Strength Machines',
                'priority' => 'High',
                'status' => 'Open',
                'reported_at' => now()->subDay(),
            ]
        );
    }
}
