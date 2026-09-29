<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->nullable()->constrained('memberships')->nullOnDelete();
            $table->string('payer_name');
            $table->string('category')->default('Membership Registration');
            $table->string('plan_label')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 30)->default('Cash');
            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
