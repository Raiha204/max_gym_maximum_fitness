<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('memberships', 'full_name')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('full_name')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('memberships', 'email')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('email')->nullable()->after('full_name');
            });
        }

        if (!Schema::hasColumn('memberships', 'phone')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('phone', 50)->nullable()->after('email');
            });
        }

        if (!Schema::hasColumn('memberships', 'gender')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('gender', 20)->default('Male')->after('phone');
            });
        }

        if (!Schema::hasColumn('memberships', 'date_of_birth')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->date('date_of_birth')->nullable()->after('gender');
            });
        }

        if (!Schema::hasColumn('memberships', 'photo_path')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('photo_path')->nullable()->after('date_of_birth');
            });
        }

        if (!Schema::hasColumn('memberships', 'plan_type')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('plan_type')->nullable()->after('photo_path');
            });
        }

        if (!Schema::hasColumn('memberships', 'duration_months')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->unsignedInteger('duration_months')->default(1)->after('plan_type');
            });
        }

        if (!Schema::hasColumn('memberships', 'monthly_rate')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->decimal('monthly_rate', 10, 2)->default(650.00)->after('duration_months');
            });
        }

        if (!Schema::hasColumn('memberships', 'total_amount')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->decimal('total_amount', 10, 2)->default(650.00)->after('monthly_rate');
            });
        }

        if (!Schema::hasColumn('memberships', 'payment_method')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('payment_method', 30)->default('Cash')->after('total_amount');
            });
        }

        if (!Schema::hasColumn('memberships', 'start_date')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('payment_method');
            });
        }

        if (!Schema::hasColumn('memberships', 'end_date')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->date('end_date')->nullable()->after('start_date');
            });
        }

        if (!Schema::hasColumn('memberships', 'status')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('status', 30)->default('Active')->after('end_date');
            });
        }

        if (!Schema::hasColumn('memberships', 'member_id')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('member_id', 12)->nullable()->after('id');
            });
        }

        // Existing membership rows do not contain enough information to
        // safely decide whether two same-named records are the same person.
        // Preserve each row as its own member during this migration.
        DB::table('memberships')->orderBy('id')->chunkById(500, function ($memberships) {
            foreach ($memberships as $membership) {
                if (empty($membership->full_name) && !empty($membership->first_name)) {
                    $fullName = trim(implode(' ', array_filter([
                        $membership->first_name ?? null,
                        $membership->last_name ?? null,
                    ])));

                    DB::table('memberships')->where('id', $membership->id)->update([
                        'full_name' => $fullName,
                    ]);
                }

                if (empty($membership->member_id)) {
                    $legacyMemberId = 'LEGACY-' . $membership->id;
                    DB::table('memberships')->where('id', $membership->id)->update(['member_id' => $legacyMemberId]);
                }
            }
        });

        if (Schema::hasColumn('memberships', 'first_name') || Schema::hasColumn('memberships', 'last_name')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('member_id', 12)->nullable(false)->change();
                $table->dropColumn(['first_name', 'last_name']);
            });
        }

        $legacyColumns = ['member_type', 'plan_name', 'amount_due', 'amount_paid', 'payment_due_date', 'penalty_applied'];
        foreach ($legacyColumns as $column) {
            if (Schema::hasColumn('memberships', $column)) {
                Schema::table('memberships', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        });

        DB::table('memberships')->orderBy('id')->chunkById(500, function ($memberships) {
            foreach ($memberships as $membership) {
                if (!empty($membership->full_name)) {
                    $parts = preg_split('/\s+/', trim($membership->full_name));
                    $firstName = $parts[0] ?? null;
                    $lastName = implode(' ', array_slice($parts, 1)) ?: null;

                    DB::table('memberships')->where('id', $membership->id)->update([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ]);
                }
            }
        });

        if (Schema::hasColumn('memberships', 'first_name') || Schema::hasColumn('memberships', 'last_name')) {
            Schema::table('memberships', function (Blueprint $table) {
                $table->string('first_name')->nullable(false)->change();
                $table->string('last_name')->nullable(false)->change();
            });
        }
    }
};
