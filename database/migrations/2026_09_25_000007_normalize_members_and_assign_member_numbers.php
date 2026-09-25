<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_number', 32)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->timestamps();
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('id')->constrained('members')->restrictOnDelete();
        });

        // Existing membership rows do not contain enough information to
        // safely decide whether two same-named records are the same person.
        // Preserve each row as its own member during this migration.
        DB::table('memberships')->orderBy('id')->chunkById(500, function ($memberships) {
            foreach ($memberships as $membership) {
                $memberId = DB::table('members')->insertGetId([
                    'member_number' => 'LEGACY-'.$membership->id,
                    'first_name' => $membership->first_name,
                    'last_name' => $membership->last_name,
                    'created_at' => $membership->created_at,
                    'updated_at' => $membership->updated_at,
                ]);

                DB::table('members')->where('id', $memberId)->update([
                    'member_number' => 'MAX-'.str_pad((string) $memberId, 8, '0', STR_PAD_LEFT),
                ]);

                DB::table('memberships')->where('id', $membership->id)->update(['member_id' => $memberId]);
            }
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable(false)->change();
            $table->dropColumn(['first_name', 'last_name']);
            $table->index(['status', 'end_date']);
            $table->index('payment_due_date');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['payment_date', 'status']);
            $table->dropForeign(['membership_id']);
            $table->foreign('membership_id')->references('id')->on('memberships')->restrictOnDelete();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['membership_id']);
            $table->foreign('membership_id')->references('id')->on('memberships')->restrictOnDelete();
        });

        Schema::table('maintenance', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
            $table->foreign('equipment_id')->references('id')->on('equipment')->restrictOnDelete();
        });

    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        });

        DB::table('memberships')->orderBy('id')->chunkById(500, function ($memberships) {
            foreach ($memberships as $membership) {
                $member = DB::table('members')->where('id', $membership->member_id)->first();
                if ($member) {
                    DB::table('memberships')->where('id', $membership->id)->update([
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name,
                    ]);
                }
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payment_date', 'status']);
            $table->dropForeign(['membership_id']);
            $table->foreign('membership_id')->references('id')->on('memberships')->cascadeOnDelete();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['membership_id']);
            $table->foreign('membership_id')->references('id')->on('memberships')->cascadeOnDelete();
        });

        Schema::table('maintenance', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->dropIndex(['status', 'end_date']);
            $table->dropIndex(['payment_due_date']);
            $table->dropConstrainedForeignId('member_id');
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
        });

        Schema::dropIfExists('members');
    }
};
