<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Job extends Model
{
    use HasFactory;

    public const WORK_TYPES = [
        'remote' => 'Remote',
        'hybrid' => 'Hybrid',
        'onsite' => 'Onsite',
    ];

    public const JOB_TYPES = [
        'full_time' => 'Full-time',
        'part_time' => 'Part-time',
        'contract' => 'Contract',
        'internship' => 'Internship',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'company_name',
        'company_website',
        'location',
        'work_type',
        'job_type',
        'job_description',
        'job_description_url',
        'baseline_salary',
        'baseline_currency',
        'status',
        'applied_at',
    ];

    protected $casts = [
        'baseline_salary' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    protected $appends = ['status_label'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(JobSession::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(JobConversation::class);
    }

    public function latestSession(): HasOne
    {
        return $this->hasOne(JobSession::class)->latestOfMany();
    }

    public function getStatusLabelAttribute(): string
    {
        return JobStatus::options()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
}
