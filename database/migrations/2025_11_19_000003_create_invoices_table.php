<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('killbill_invoice_id')->nullable();
            $table->string('number')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('amount_subscription', 10, 2)->default(0);
            $table->decimal('amount_ai_usage', 10, 2)->default(0);
            $table->decimal('amount_total', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
