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
            // Set for a membership payment (initial or balance top-up). Null for a walk-in.
            $table->foreignId('membership_id')->nullable()->constrained()->onDelete('cascade');
            // Set for a walk-in payment, to know which preset price applied (Regular ₱65 / Student ₱50).
            $table->enum('visitor_type', ['regular', 'student'])->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 8, 2);
            $table->enum('payment_method', ['cash', 'gcash', 'other'])->default('cash');
            $table->enum('status', ['completed', 'refunded'])->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
