<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('member_id', 12)->unique();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
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

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
