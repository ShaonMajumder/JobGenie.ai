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
        Schema::create('job_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->decimal('input_baseline_salary', 12, 2)->nullable();
            $table->string('input_currency', 3)->nullable();
            $table->longText('input_resume_text')->nullable();
            $table->longText('generated_cover_letter')->nullable();
            $table->longText('generated_tailored_resume')->nullable();
            $table->json('salary_suggestions')->nullable();
            $table->unsignedTinyInteger('ats_score')->nullable();
            $table->longText('ats_feedback')->nullable();
            $table->json('raw_llm_request')->nullable();
            $table->json('raw_llm_response')->nullable();
            $table->timestamps();

            $table->index(['job_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_sessions');
    }
};
