<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('company_name');
            $table->string('company_website')->nullable();
            $table->string('location')->nullable();
            $table->string('work_type')->default('remote');
            $table->string('job_type')->default('full_time');
            $table->text('job_description');
            $table->string('job_description_url')->nullable();
            $table->decimal('baseline_salary', 12, 2)->nullable();
            $table->string('baseline_currency', 3)->default('USD');
            $table->string('status')->default('not_applied');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
