<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_member_photo_is_available_on_the_member_pass(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('members/member.jpg', 'photo contents');

        $membership = Membership::create([
            'member_id' => '123456',
            'full_name' => 'Member, Test',
            'email' => 'member@example.com',
            'phone' => '09123456789',
            'gender' => 'Other',
            'date_of_birth' => '2000-01-01',
            'photo_path' => 'members/member.jpg',
            'plan_type' => 'Student Membership',
            'duration_months' => 1,
            'monthly_rate' => 650,
            'total_amount' => 650,
            'payment_method' => 'Cash',
            'start_date' => '2026-10-05',
            'end_date' => '2026-11-05',
            'status' => 'Active',
        ]);

        $this->actingAs(User::factory()->create())
            ->get($membership->photo_url)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertStreamedContent('photo contents');
    }
}
