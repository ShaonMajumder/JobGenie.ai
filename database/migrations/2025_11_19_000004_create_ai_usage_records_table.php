<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_session_id')->nullable()->constrained('job_sessions')->nullOnDelete();
            $table->string('provider');
            $table->string('model');
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->unsignedBigInteger('total_tokens')->default(0);
            $table->decimal('cost_input', 12, 6)->default(0);
            $table->decimal('cost_output', 12, 6)->default(0);
            $table->decimal('cost_total', 12, 6)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->foreignId('billed_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_records');
    }
};
