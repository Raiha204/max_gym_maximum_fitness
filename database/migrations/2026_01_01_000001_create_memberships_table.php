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
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('member_type', ['regular', 'student'])->default('regular');
            $table->string('plan_name')->default('Monthly');
            $table->decimal('amount_due', 8, 2);
            $table->decimal('amount_paid', 8, 2)->default(0);
            $table->date('payment_due_date')->nullable();
            $table->boolean('penalty_applied')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            // pending: no/partial payment and not yet started
            // active: fully paid and within date range
            // expired: end_date has passed
            $table->enum('status', ['pending', 'active', 'expired'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
