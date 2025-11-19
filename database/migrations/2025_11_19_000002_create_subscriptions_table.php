<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans');
            $table->string('killbill_account_id')->nullable();
            $table->string('killbill_subscription_id')->nullable();
            $table->string('status')->default('inactive');
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('grace_period_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
