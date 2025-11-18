<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'input_baseline_salary',
        'input_currency',
        'input_resume_text',
        'generated_cover_letter',
        'generated_tailored_resume',
        'salary_suggestions',
        'ats_score',
        'ats_feedback',
        'raw_llm_request',
        'raw_llm_response',
    ];

    protected $casts = [
        'input_baseline_salary' => 'decimal:2',
        'salary_suggestions' => 'array',
        'raw_llm_request' => 'array',
        'raw_llm_response' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
